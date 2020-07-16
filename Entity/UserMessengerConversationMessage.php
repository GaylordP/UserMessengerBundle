<?php

namespace GaylordP\UserMessengerBundle\Entity;

use App\Entity\User;
use App\Entity\UserMedia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use GaylordP\UserBundle\Annotation\CreatedAt;
use GaylordP\UserBundle\Annotation\CreatedBy;
use GaylordP\UserBundle\Entity\Traits\Deletable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserMessengerConversationMessage
 *
 * @ORM\Entity(repositoryClass="GaylordP\UserMessengerBundle\Repository\UserMessengerConversationMessageRepository")
 */
class UserMessengerConversationMessage
{
    use Deletable;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @var UserMedia[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\UserMedia",
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"id": "DESC"})
     */
    private $userMedias;

    /**
     * @var UserMessengerConversation
     *
     * @ORM\ManyToOne(
     *     targetEntity="GaylordP\UserMessengerBundle\Entity\UserMessengerConversation",
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
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User"
     * )
     * @CreatedBy
     */
    private $createdBy;

    public function __construct()
    {
        $this->userMedias = new ArrayCollection();
    }

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
     * Get message
     *
     * @return string 
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Set message
     * 
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * Get user medias
     * 
     * @return UserMedia[]|ArrayCollection
     */
    public function getUserMedias(): Collection
    {
        return $this->userMedias;
    }

    /**
     * Add user media
     * 
     * @param UserMedia userMedia
     */
    public function addUserMedia(UserMedia $userMedia): void
    {
        $this->userMedias->add($userMedia);
    }

    /**
     * Remove user media
     * 
     * @param UserMedia $userMedia
     */
    public function removeUserMedia(UserMedia $userMedia): void
    {
        $this->userMedias->removeElement($userMedia);
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
}
