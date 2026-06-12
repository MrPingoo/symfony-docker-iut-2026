<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objet de transfert (DTO) représentant les données ENTRANTES d'un produit.
 *
 * Pourquoi un DTO plutôt que désérialiser directement dans l'entité ?
 *  - on découple la représentation API de la structure en base ;
 *  - on gère proprement la relation (categoryId : un simple entier) ;
 *  - on valide les entrées sans polluer l'entité Doctrine.
 */
class ProductInput
{
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    #[Assert\Length(min: 2, max: 255)]
    public ?string $name = null;

    public ?string $description = null;

    #[Assert\NotNull(message: 'Le prix est obligatoire.')]
    #[Assert\Positive(message: 'Le prix doit être strictement positif.')]
    public ?int $priceCents = null;

    #[Assert\NotNull(message: 'Le stock est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif.')]
    public ?int $stock = 0;

    #[Assert\NotNull(message: 'La catégorie (categoryId) est obligatoire.')]
    public ?int $categoryId = null;
}
