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
     * Create order page. Create an order consisting of 1..n cards
     * This view handles forms that have been posted. Verify information is valid for each card in the order
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

        //Get official list of options for sports, manufacturers, and years in array form
        $officialSports = $this->getSports();
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

            /*
            * Each section of data is separated into it's own array, iterate through it and create an array for each card row
            */
            for($i = 0; $i < $rows; $i++)
            {
                $card = [
                    "sport" => $sports[$i],
                    "playerName" => $playerNames[$i],
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

            //Add all the cards from this order to the database
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
     * Create order page. This will generate the order form with 1 single card. 
     * Users can dynamically add/remove additional cards using the jquery buttons
     */
    public function createOrderView(): Response
    {
        //Only allow logged in users to see this page
        if (!$this->getUser()) 
        {
             return $this->redirectToRoute('login');
        }

        //Get official list of sports, manufacturers, and years in array form
        $officialSports = $this->getSports();
        $officialYears = $this->getYears();
        $officialManufacturers = $this->getManufacturers();

        return $this->createView($officialSports,$officialYears,$officialManufacturers,1,[]);
    }

    /**
    * Helper function that sets up the variables that should be sent to twig based on whether form should be blank, contain filled in rows, etc.
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
     * View orders for a given user.
     * If no userID is specified, then it will pull the logged in user's orders
     * If a userID is specified, 
     */
    public function viewOrders(Request $request, $userID): Response
    {
        //Only allow logged in users to see this page
        if (!$this->getUser()) 
        {
             return $this->redirectToRoute('login');
        }

        $pageTitle = "View Orders";
        $yourID = $this->getUser()->getUserId();

        //No userID given or userID belongs to the logged in user. So pull our own orders 
        if($userID == -1 || $yourID == $userID){
            $userID = $this->getUser()->getUserId();
            $pageTitle = 'Your Orders';
        }
        else
        {
            //Make sure that our permissions are higher than this users and we are actually allowed to view their orders 
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

        //Extract all orders for this user into a twig friendly format. Also pull cards for each order
        foreach($rawOrders as $rawOrder)
        {
            //Orders array that we are going to pass to twig
            $order = [
                'id' => $rawOrder->getOrderId(),
                'cards' => []
            ];

            //Get all cards for this order
            $rawCards = $this->getDoctrine()->getRepository(Cards::class)->findBy([
                'order' => $rawOrder->getOrderId()
            ]);
            //Extract each card into a twig readable format
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
    * Return an array of all sports that are options
    */
    private function getSports(): array
    {
        return ["Football","Baseball","Basketball","Hockey","Soccer"];
    }

    /**
    * Returns an array of years for the year dropdown
    * Years are in the form of 2021, 2021-22, etc.
    * Years start at 1960 and go to 2021
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
