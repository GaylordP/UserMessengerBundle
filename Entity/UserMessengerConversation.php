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
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

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
     * Get uuid
     *
     * @return string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     */
    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
