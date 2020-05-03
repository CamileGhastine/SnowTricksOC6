<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class AvatarProto
{
    /**
     * @Assert\NotBlank(message="Aucun fichier n'a été téléversé.")
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "L'image doit être au format jpeg ou png"
     * )
     */
    private $file;

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
}
