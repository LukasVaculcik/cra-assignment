<?php

declare(strict_types=1);

namespace App\AdminModule\Components;

use App\Model\AnimalRepository;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class AnimalForm extends Control
{

    public const
        FIELD_NAME = "name",
        FIELD_TYPE = "type",
        FIELD_SUBMIT = "submit";

    public $onSuccess = null;
    public $onError = null;

    public function __construct(
        private readonly AnimalRepository $animalRepository,
        private readonly ?ActiveRow $defaultValues,
        $onSuccess,
        $onError
    ) {
        $this->onSuccess = $onSuccess;
        $this->onError = $onError;
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/AnimalForm.latte');
        $this->template->render();
    }

    public function createComponentForm(): Form
    {
        $types = $this->animalRepository->findAnimalTypes()->fetchPairs(AnimalRepository::COLUMN_TYPE_ID, AnimalRepository::COLUMN_TYPE_NAME);
        $defaultTypeId = null;
        if ($this->defaultValues) {
            $animalHasType = $this->animalRepository->findAnimalHasType()->where(AnimalRepository::COLUMN_HAS_TYPE_PARENT, $this->defaultValues[AnimalRepository::COLUMN_TYPE_ID])->fetch();
            $defaultTypeId = $animalHasType[AnimalRepository::COLUMN_HAS_TYPE_ID];
        }
        bdump($defaultTypeId);
        $form = new Form();

        $form->addText(self::FIELD_NAME, 'Animal name')
            ->setRequired(true);

        $form->addSelect(self::FIELD_TYPE, 'Type', $types)
            ->setDefaultValue($defaultTypeId)
            ->setPrompt('Choose type');

        $form->addSubmit(self::FIELD_SUBMIT, 'Save');

        if ($this->defaultValues) {
            $form->setDefaults($this->defaultValues);
        }

        $form->onSuccess[] = function (Form $form, array $values) {
            if ($this->onSuccess) {
                ($this->onSuccess)($values);
            }
        };

        return $form;
    }
}
