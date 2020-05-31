<?php

namespace App\Service;

class GetErrorsMessageService
{
    /**
     * @param $form
     *
     * @return array
     */
    public function getMessage($form)
    {
        $errors = [];

        if (0 == $form->count()) {
            return $errors;
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = str_replace('ERROR: ', '', (string) $form[$child->getName()]->getErrors());
            }
        }

        return $errors;
    }
}
