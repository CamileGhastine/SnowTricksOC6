<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 */
class Video
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *     pattern = "/^(<iframe\s).*src=.*(<\/iframe>)$/i",
     *     message = "La valeur entrée n'est pas un format iframe valide"
     * )
     */
    private $iframe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="videos")
     */
    private $trick;

    /**
     * Refactor the iframe paste by user.
     */
    public function refactorIframe()
    {
        $array = explode(' ', $this->iframe);
        foreach ($array as $value) {
            if (strstr($value, 'src="')) {
                $this->iframe = '<iframe '.$value.'></iframe>';
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIframe(): ?string
    {
        return $this->iframe;
    }

    public function setIframe(string $iframe): self
    {
        $this->iframe = $iframe;

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
