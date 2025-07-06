<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\Statut;
use App\Repository\AchatRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['achat:read'])]
    private ?int $id = null;

    #[Groups(['achat:read'])]
    #[ORM\ManyToOne(inversedBy: 'achats')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'achats')]
    #[Groups(['achat:read'])]
    private ?Produit $produit = null;

    #[Groups(['achat:read'])]
    #[ORM\Column]
    private ?int $quantite = null;

    #[Groups(['achat:read'])]
    #[ORM\Column]
    private ?\DateTime $dateAchat = null;

    #[ORM\Column(enumType: Statut::class)]
    #[Groups(['achat:read'])]
    private Statut $statut;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDateAchat(): ?\DateTime
    {
        return $this->dateAchat;
    }

    public function setDateAchat(\DateTime $dateAchat): static
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(Statut $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    #[Groups(['achat:read'])]
    public function getPrixTotal(): ?float
    {
        if ($this->produit && $this->quantite !== null) {
            return $this->produit->getPrix() * $this->quantite;
        }

        return null;
    }
}
