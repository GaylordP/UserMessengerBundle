<?php

namespace GaylordP\UserMessengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserMessengerConversation
 *
 * @ORM\Entity(repositoryClass="GaylordP\UserMessengerBundle\Repository\UserMessengerConversationRepository")
 */
class UserMessengerConversation
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="_group")
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $token;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set group
     *
     * @param bool $group
     */
    public function setGroup(?bool $group): void
    {
        $this->group = $group;
    }

    /**
     * Get group
     *
     * @return bool
     */
    public function getGroup(): ?bool
    {
        return $this->group;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
