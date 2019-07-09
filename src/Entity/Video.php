<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 * @ORM\Table(name="videos", indexes={@Index(name="title_idx", columns={"title"})})
 */
class Video
{
    //public const videoForNotLoggedIn = 113716040; // vimeo id 
    // public const videoForNotLoggedInOrNoMembers = 113716040; // vimeo id 
    public const videoForNotLoggedInOrNoMembers = 'https://player.vimeo.com/video/'; // vimeo id 

    public const VimeoPath = 'https://player.vimeo.com/video/';
    public const perPage = 5; // for pagination
    public const uploadFolder = '/uploads/videos/';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="videos")
     * //@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="video")
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="videos")
     * @ORM\JoinTable(name="likes")
     */
    private $usersThatLike;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="dislikedVideos")
     * @ORM\JoinTable(name="dislikes")
     */
    private $usersThatDonotLike;

    /** 
     * @Assert\NotBlank(message="Please, upload the video as a MP4 file.")
     * @Assert\File(mimeTypes={"video/mp4"}) 
     */
    private $uploaded_video;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->usersThatLike = new ArrayCollection();
        $this->usersThatDonotLike = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    //set the path
    public function getVimeoId(): ?string
     {
    //     if($user) //if user is logged
    //     {
    //         return $this->path;
    //     }
    //     else 
    //     {
    //         return self::VimeoPath.self::videoForNotLoggedIn;
    //     }
        return $this->path;
    }


    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setVideo($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getVideo() === $this) {
                $comment->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsersThatLike(): Collection
    {
        return $this->usersThatLike;
    }

    public function addUsersThatLike(User $usersThatLike): self
    {
        if (!$this->usersThatLike->contains($usersThatLike)) {
            $this->usersThatLike[] = $usersThatLike;
        }

        return $this;
    }

    public function removeUsersThatLike(User $usersThatLike): self
    {
        if ($this->usersThatLike->contains($usersThatLike)) {
            $this->usersThatLike->removeElement($usersThatLike);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsersThatDonotLike(): Collection
    {
        return $this->usersThatDonotLike;
    }

    public function addUsersThatDonotLike(User $usersThatDonotLike): self
    {
        if (!$this->usersThatDonotLike->contains($usersThatDonotLike)) {
            $this->usersThatDonotLike[] = $usersThatDonotLike;
        }

        return $this;
    }

    public function removeUsersThatDonotLike(User $usersThatDonotLike): self
    {
        if ($this->usersThatDonotLike->contains($usersThatDonotLike)) {
            $this->usersThatDonotLike->removeElement($usersThatDonotLike);
        }

        return $this;
    }

    public function getUploadedVideo()
    {
        return $this->uploaded_video;
    }

    public function setUploadedVideo($uploaded_video): self
    {
        $this->uploaded_video = $uploaded_video;

        return $this;
    }
}