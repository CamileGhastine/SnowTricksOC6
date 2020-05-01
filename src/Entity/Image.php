<?php

namespace App\Entity;

use App\Kernel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $alt;

    /**
     * if $poster=1 the image is the first image
     * @ORM\Column(type="boolean")
     */
    private $poster;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="images")
     */
    private $trick;

    /**
     * @var UploadedFile
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "L'image doit être au format jpeg ou png"
     * )
     */
    private $file;

    /**
     * @param SluggerInterface $slugger
     */
    public function upload(SluggerInterface $slugger)
    {
        $OriginalName = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = $slugger->slug($OriginalName).'-'.uniqid().'.'.pathinfo($this->file->getClientOriginalName(), PATHINFO_EXTENSION);

        $this->file->move(Kernel::getProjectDir().'/public/images/tricks', $name);

        $this->setUrl('images/tricks/'.$name);
        $this->setAlt($name);
        $this->setPoster(0);
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getPoster(): ?bool
    {
        return $this->poster;
    }

    public function setPoster(bool $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
