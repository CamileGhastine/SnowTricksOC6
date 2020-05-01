<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


class UserProto
{

    private $id;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Votre nom d'utilisateur doit être composé de {{ limit }} charactères minimum.",
     *      maxMessage = "Votre nom d'utilisateur doit être composé de {{ limit }} charactères maximum.",
     * )
     */
    private $username;

    /**
     * @Assert\Email(
     *     message = "Ce courriel n'est pas valide."
     * )
     */
    private $email;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Votre mot de passe doit être composé de {{ limit }} charactères minimum.",
     *      maxMessage = "Votre mot de passe doit être composé de {{ limit }} charactères maximum.",
     * )
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath= "password",
     *     message= "Les deux mots de passe sont différents.")
     */
    private $confirm_password;

    /**
     * @Assert\Url
     */
    private $avatar;

    private $role='ROLE_USER';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmPassword()
    {
        return $this->confirm_password;
    }

    /**
     * @param mixed $confirm_password
     */
    public function setConfirmPassword($confirm_password): void
    {
        $this->confirm_password = $confirm_password;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return string[] The user roles
     */
    public function getRoles()
    {
        return [$this->role] ;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
