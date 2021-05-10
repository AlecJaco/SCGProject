<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Orders;
use App\Entity\Cards;
use App\Entity\Manufacturers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
class OrderController extends AbstractController
{
    /**
     * @Route("/orders", name="ordersPost", methods="POST")
     * Handle post request, verify that all information entered is valid. If it isn't give an error. If it is then create order
     */
    public function createOrderPost(Request $request): Response
    {
        //Only allow logged in users to see this page
        if (!$this->getUser()) 
        {
             return $this->redirectToRoute('login');
        }

        $cards = [];

        //Get first column of input to see how many rows the user submitted
        $playerNames = $request->get("playerName");
        $rows = count($playerNames);

        //Get official list of sports, manufacturers, and years in array form
        $officialSports = ["Football","Baseball","Basketball","Hockey","Soccer"];
        $officialYears = $this->getYears();
        $officialManufacturers = $this->getManufacturers();

        //The post data did not contain any pertinent information. Render a blank order form with 1 row
        if($rows == 0)
        {
            return $this->createOrderView();
        }
        else
        {
            //Seperate out all post data that we need
            $sports = $request->get("sport");
            $years = $request->get("year");
            $manufacturers = $request->get("manufacturer");
            $numbers = $request->get("number");
            $setNames = $request->get("setName");
            $variations = $request->get("variation");
            $values = $request->get("value");

            //Keeps track of how many rows of input contained errors
            $errors = 0;

            //Create an array of cards from the post data
            for($i = 0; $i < $rows; $i++)
            {
                $card = [
                    "playerName" => $playerNames[$i],
                    "sport" => $sports[$i],
                    "year" => $years[$i],
                    "manufacturer" => $manufacturers[$i],
                    "number" => $numbers[$i],
                    "setName" => $setNames[$i],
                    "variation" => $variations[$i],
                    "value" => $values[$i]
                ];
                
                //Check to see if there are any errors with this row of input
                $card["errorStr"] = $this->validateCard($card,$officialSports,$officialYears,$officialManufacturers);
                if($card["errorStr"] != "")
                {
                    $errors++;
                }

                array_push($cards,$card);
            }
        }

        //There were no errors with any of the rows of input. So create the order
        if($errors == 0)
        {
            $entityManager = $this->getDoctrine()->getManager();

            //Create order and add it to the database
            $order = new Orders();
            $order->setUser($this->getUser());
            $entityManager->persist($order);
            $entityManager->flush();

            foreach($cards as $cardInfo)
            {
                //Get manufacturer object
                $manufacturer = $this->getDoctrine()->getRepository(Manufacturers::class)->find($cardInfo['manufacturer']);

                //Create card and add it to the database
                $card = new Cards();
                $card->setOrder($order);
                $card->setName($cardInfo['playerName']);
                $card->setSport($cardInfo['sport']);
                $card->setYear($cardInfo['year']);
                $card->setManufacturer($manufacturer);
                $card->setCardNumber($cardInfo['number']);
                $card->setSetName($cardInfo['setName']);
                $card->setVariation($cardInfo['variation']);
                $card->setDeclaredValue($cardInfo['value']);
                $entityManager->persist($card);
                $entityManager->flush();
            }

            //Since there were no errors, show the user the original blank form, along with a success message
            return $this->createView($officialSports,$officialYears,$officialManufacturers,1,[],"Successfully created your order!");
        }
        //There were errors, so return the form the user submitted along with row specific error messages
        else
        {
            return $this->createView($officialSports,$officialYears,$officialManufacturers,$rows,$cards);
        }
    }

    /**
     * @Route("/orders", name="orders")
     * Generates blank form with 1 row
     */
    public function createOrderView(): Response
    {
        //Only allow logged in users to see this page
        if (!$this->getUser()) 
        {
             return $this->redirectToRoute('login');
        }

        //Get official list of sports, manufacturers, and years in array form
        $officialSports = ["Football","Baseball","Basketball","Hockey","Soccer"];
        $officialYears = $this->getYears();
        $officialManufacturers = $this->getManufacturers();

        return $this->createView($officialSports,$officialYears,$officialManufacturers,1,[]);
    }

    /**
    * Sets up the variables that should be sent to twig based on whether form should be blank, contain filled in rows, etc.
    * Then will render the view
    */
    private function createView(array $officialSports, array $officialYears, array $officialManufacturers, int $rows, array $cards, string $successMsg = ""): Response
    {
        $twigArray = [
            'sports' => $officialSports,
            'years' => $officialYears,
            'manufacturers' => $officialManufacturers,
            'rows' => $rows,
            'successMsg' => $successMsg,
            'page' => 'orders'
        ];

        if(count($cards) > 0)
        {
            $twigArray['cards'] = $cards;
        }

        return $this->render('order/index.html.twig', $twigArray);
    }

    /**
     * @Route("/view-orders/{userID}", name="view-orders", defaults={"userID"=-1}, requirements={"userID"="\d+"})
     * View orders that we have posted
     */
    public function viewOrders(Request $request, $userID): Response
    {
        $pageTitle = "View Orders";
        //No userID given, check orders for current user
        if($userID == -1){
            $userID = $this->getUser()->getUserId();
            $pageTitle = 'Your Orders';
        }
        else
        {
            //Make sure that our permissions are higher than this users and we area actually allowed to view their orders 
            $user = $this->getDoctrine()->getRepository(Users::class)->find($userID);

            //If user wasn't found or we don't have atleast 1 higher permission level, then we can't view their profile
            if($user == null || $this->getUser()->getPermissionLevel() <= $user->getPermissionLevel())
            {
                return $this->redirectToRoute('view-orders');
            }
        }

        //Gett all orders from this user
        $rawOrders = $this->getDoctrine()->getRepository(Orders::class)->findBy([
            'user' => $userID
        ]);

        $orders = [];

        //Extract all orders for this user into a twig friendly form. Also pull cards for each order
        foreach($rawOrders as $rawOrder)
        {
            $order = [
                'id' => $rawOrder->getOrderId(),
                'cards' => []
            ];

            //Get all cards for this order
            $rawCards = $this->getDoctrine()->getRepository(Cards::class)->findBy([
                'order' => $rawOrder->getOrderId()
            ]);
            foreach($rawCards as $rawCard)
            {
                $card = [
                    'player' => $rawCard->getName(),
                    'sport' => $rawCard->getSport(),
                    'year' => $rawCard->getYear(),
                    'manufacturer' => $rawCard->getManufacturer()->getName(),
                    'number' => $rawCard->getCardNumber(),
                    'setName' => $rawCard->getSetName(),
                    'variations' => $rawCard->getVariation(),
                    'value' => $rawCard->getDeclaredValue()
                ];
                array_push($order['cards'],$card);
            }

            array_push($orders,$order);
        }

        //Show newest orders first
        $orders = array_reverse($orders);

        return $this->render('order/viewOrders.html.twig', [
            'page' => 'view-orders',
            'orders' => $orders,
            'pageTitle' => $pageTitle
        ]);
    }

    /**
    * Returns an array of years for the year dropdown
    */
    private function getYears(): array
    {
        $yearInt = 1960;
        $years = [];

        for($i = 1960; $i <= 2021; $i++)
        {
            //First year should be one less than the current year
            array_push($years,($i-1)."");

            //Second year should include the first year. And the last two digits of the next year
            array_push($years,($i-1)."-".substr($i."",2));
        }

        //Reverse years so that the current years are shown first
        $years = array_reverse($years);
        
        return $years;
    }

    /**
    * Returns associative array of manufacturers with their manufacturer_ids and names
    */
    private function getManufacturers(): array
    {
        $query =
            '
            SELECT 
                 manufacturer_id as id, name
            FROM 
                manufacturers
            ORDER BY
                name ASC
            ';
        $entityManager = $this->getDoctrine()->getManager();
        $statement = $entityManager->getConnection()->prepare($query);
        $statement->execute();
        
        $rows = $statement->fetchAll();

        return $rows;
    }

    /**
    * Validates that the information for the card is in the correct format and valid. 
    * Return the error string if there is one
    */
    private function validateCard(array $card, array $sports, array $years, array $manufacturers): string
    {
        //Make sure player name isn't empty
        if(empty(trim($card['playerName'])))
        {
            return "Name must be given";
        }
        //Gave a sport that isn't in the sports array
        else if(!in_array($card['sport'],$sports))
        {
            return "Invalid sport";
        }
        //Gave a year that isn't in the year array
        else if(!in_array($card['year'],$years)){
            return "Invalid year";
        }
        //Gave a manufacturer id that isn't in the manufacturer array
        else if(!in_array($card['manufacturer'],array_column($manufacturers, 'id'))){
            return "Invalid manufacturer";
        }
        //Make sure card number isn't empty
        else if(empty(trim($card['number'])))
        {
            return "Card # must be given";
        }
        //Make sure set name isn't empty
        else if(empty(trim($card['setName'])))
        {
            return "Set must be given";
        }
        //Make sure variation isn't empty
        else if(empty(trim($card['variation'])))
        {
            return "Variation must be given";
        }
        //Make sure value is a number and positive
        else if(!is_numeric($card['value']) || $card['value'] <= 0)
        {
            return "Value must be positive number";
        }

        //Make sure value has at most 2 decimal points
        //Converts value to string, gets substring of characters after decimal point
        $decimalPrecision = substr($card['value'],strpos($card['value'],'.')+1);
        if(strlen($decimalPrecision) > 2)
        {
            return "Value can have at most 2 decimal precision";
        }

        return "";
    }
}
