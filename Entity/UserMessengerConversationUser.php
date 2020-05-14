<?php

namespace GaylordP\UserMessengerBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use GaylordP\UserBundle\Annotation\CreatedAt;
use GaylordP\UserBundle\Annotation\CreatedBy;
use GaylordP\UserBundle\Entity\Traits\Deletable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserMessengerConversationUser
 *
 * @ORM\Entity(repositoryClass="GaylordP\UserMessengerBundle\Repository\UserMessengerConversationUserRepository")
 */
class UserMessengerConversationUser
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
     * @var User
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     * )
     * @Assert\NotBlank()
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readAt;

    /**
     * @var Conversation
     *
     * @ORM\ManyToOne(
     *     targetEntity="GaylordP\UserMessengerBundle\Entity\UserMessengerConversation"
     * )
     */
    private $userMessengerConversation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @CreatedAt
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @CreatedBy
     */
    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deletedBeforeAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    protected $deletedBeforeBy;

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
     * Get user
     * 
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set user
     * 
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get readAt
     * 
     * @return \DateTime
     */
    public function getReadAt(): ?\DateTime
    {
        return $this->readAt;
    }

    /**
     * Set readAt
     * 
     * @param \DateTime $date
     */
    public function setReadAt(?\DateTime $date): void
    {
        $this->readAt = $date;
    }

    /**
     * Get userMessengerConversation
     * 
     * @return UserMessengerConversation
     */
    public function getUserMessengerConversation(): ?UserMessengerConversation
    {
        return $this->userMessengerConversation;
    }

    /**
     * Set userMessengerConversation
     * 
     * @param UserMessengerConversation $userMessengerConversation
     */
    public function setUserMessengerConversation(?UserMessengerConversation $userMessengerConversation)
    {
        $this->userMessengerConversation = $userMessengerConversation;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $date
     */
    public function setCreatedAt(\DateTime $date): void
    {
        $this->createdAt = $date;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param User $user
     */
    public function setCreatedBy(User $user): void
    {
        $this->createdBy = $user;
    }

    /**
     * Get deleted before at
     *
     * @return \DateTime
     */
    public function getDeletedBeforeAt(): ?\DateTime
    {
        return $this->deletedBeforeAt;
    }

    /**
     * Set deleted before at
     *
     * @param \DateTime $date
     */
    public function setDeletedBeforeAt(?\DateTime $date): void
    {
        $this->deletedBeforeAt = $date;
    }

    /**
     * Get deleted before by
     *
     * @return User
     */
    public function getDeletedBeforeBy(): ?User
    {
        return $this->deletedBeforeBy;
    }

    /**
     * Set deleted before by
     *
     * @param User $user
     */
    public function setDeletedBeforeBy(?User $user): void
    {
        $this->deletedBeforeBy = $user;
    }
}
