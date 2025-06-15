<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['achat:read'])]
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;
    #[Groups(['achat:read'])]
    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Categorie $categorie = null;

    /**
     * @var Collection<int, Achat>
     */
    #[ORM\OneToMany(targetEntity: Achat::class, mappedBy: 'produit')]
    private Collection $achats;

    public function __construct()
    {
        $this->achats = new ArrayCollection();
        // Définit la catégorie par défaut à l'ID 6
        // Attention : ceci suppose que l'entité Categorie avec id=6 existe déjà
        // et que l'EntityManager ou un repository est accessible ici.
        // Sinon, il faut injecter la catégorie lors de la création du Produit.
        // Exemple d'initialisation simple (non recommandé en production) :
        // $this->categorie = (new Categorie())->setId(6);

        // Pour une vraie application, il faut injecter la catégorie depuis le service ou le contrôleur.
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Achat>
     */
    public function getAchats(): Collection
    {
        return $this->achats;
    }

    public function addAchat(Achat $achat): static
    {
        if (!$this->achats->contains($achat)) {
            $this->achats->add($achat);
            $achat->setProduit($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): static
    {
        if ($this->achats->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getProduit() === $this) {
                $achat->setProduit(null);
            }
        }

        return $this;
    }
}
