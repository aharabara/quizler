<?php

namespace App\Representation\Handler;

use App\Representation\RepresentationType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

#[AsTaggedItem(10)]
class FormSubmissionRepresentationHandler extends RedirectRepresentationHandler
{
    public function getType(): RepresentationType
    {
        return RepresentationType::FORM_SUBMITTED;
    }

    public function supports(Request $request, array $data, array $representations): bool
    {
        $form = $this->thereIsASubmittedForm($data);

        if ($form && $form->isValid()) {
            return true;
        }

        return false;
    }


    private function thereIsASubmittedForm(array $parameters): ?FormInterface
    {
        foreach ($parameters as $v) {
            if ($v instanceof FormInterface && $v->isSubmitted()) {
                return $v;
            }
        }

        return null;
    }
}
