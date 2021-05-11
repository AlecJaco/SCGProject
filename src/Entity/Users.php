<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="email", columns={"email_address"})})
 * @ORM\Entity
 */
class Users implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=320, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $emailAddress;

    /**
    * @Assert\NotBlank()
    * @Assert\Length(max=4096)
    */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=1024, nullable=false)
     */
    private $password;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="first_name", type="string", length=128, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="last_name", type="string", length=128, nullable=false)
     */
    private $lastName;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @ORM\Column(name="register_timestamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $registerTimestamp;
    
    /**
     * @var array
     * @ORM\Column(name="roles", type="json", nullable=false)
     */
    private $roles;

    public function __construct()
    {
        $this->registerTimestamp = new \Datetime();
        $this->roles = [];
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
    * Return any errors that there are with the password. Returns the error, or an empty string if it is valid 
    * Passwords must be at least 8 characters long, contain 1+ uppercase character, 1+ lowercase character, 1+ number, and 1+ symbol 
    */
    public function checkPassword(): ?string
    {
        if (strlen($this->plainPassword) < 8) {
            return "Password must be at least 8 characters long";
        }
        else if (!preg_match("/\d/", $this->plainPassword)) {
            return "Password must contain at least one number";
        }
        else if (!preg_match("/[A-Z]/", $this->plainPassword)) {
            return "Password must contain at least one uppercase letter";
        }
        else if (!preg_match("/[a-z]/", $this->plainPassword)) {
            return "Password must contain at least one lowercase letter";
        }
        else if (!preg_match("/\W/", $this->plainPassword)) {
            return "Password must contain at least one special character";
        }
        else if (preg_match("/\s/", $this->plainPassword)) {
            return "Password cannot contain any white space";
        }

        return "";
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function getSalt(): ?string
    {
        return "";
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRegisterTimestamp(): ?\DateTimeInterface
    {
        return $this->registerTimestamp;
    }

    public function setRegisterTimestamp(\DateTimeInterface $registerTimestamp): self
    {
        $this->registerTimestamp = $registerTimestamp;

        return $this;
    }

    /**
    * Add the given role to the user's permissions
    */
    public function addRole(string $role): self
    {
        $this->roles[] = $role;
        $this->roles = array_unique($this->roles);

        return $this;
    }

    /**
    * Remove the given role
    */
    public function removeRole(string $role): self
    {
        $this->roles = array_diff($this->roles, [$role]);

        return $this;
    }

    /**
    * Following functions implement the UserInterface functions that aren't needed
    */
    public function getRoles(): ?array
    {
        $roles = $this->roles;
        $roles[] = "ROLE_USER";
        $roles = array_unique($roles);

        return $roles;
    }

    public function getUsername(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * Removes sensitive data from the user
     */
    public function eraseCredentials()
    {
        $this->setPlainPassword("");
    }

    /*
    * Get the clearance level for this permission type in integer form
    * Used for comparing roles against each other
    */
    public function getPermissionLevel() : int
    {
        if(in_array('ROLE_ADMIN',$this->roles))
        {
            return 4;
        }
        else  if(in_array('ROLE_OWNER',$this->roles))
        {
            return 3;
        }
        else  if(in_array('ROLE_MANAGER',$this->roles))
        {
            return 2;
        }

        return 1;
    }
}
