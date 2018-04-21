<?php

namespace CreamIO\UploadBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class UserStoredFile
{
    /**
     * Auto increment id.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * Complete path to the uploaded file.
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="No file provided.")
     * @Assert\File()
     */
    protected $file;

    /**
     * Creation time.
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * UserStoredFile constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Id getter.
     *
     * @return null|integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * File path getter.
     *
     * @return null|string
     */
    public function getFile(): ?string
    {
        return \basename($this->file);
    }

    /**
     * File path setter.
     *
     * @param string $file
     *
     * @return UserStoredFile
     */
    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Creation time getter.
     *
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
