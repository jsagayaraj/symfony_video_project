<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="comments")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Video", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false, name="video_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $video;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }
    
    
    // this @ORM\PrePersist (LifecycleCallbacks) means this function setCreatedAt automatically creats the actual date when submitting the form without creating the form entries 
    /**
     * @ORM\PrePersist
     */
    public function setCretaedAt(): self
    {
        if(isset($this->created_at2))
        {
            $this->created_at = $this->created_at2;    
        }
        else
        {
            $this->created_at = new \DateTime();
        }
        return $this;
    }

    // this only for fixtures(fake data) which creats some past dummy dates; this is the only helper method
    public function setCreatedAtForFixtures($created_at): self
    {
        $this->created_at2 = $created_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): self
    {
        $this->video = $video;

        return $this;
    }
}
