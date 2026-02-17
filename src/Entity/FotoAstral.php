<?php

namespace App\Entity;

use App\Repository\FotoAstralRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FotoAstralRepository::class)]
#[ORM\Table(name: 'foto_astral', schema: 'apodnasa')]
class FotoAstral
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, unique: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $copyright = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $explanation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hdurl = null;

    #[ORM\Column(name: "media_type", length: 100)]
    private ?string $mediaType = null;

    #[ORM\Column(name: "service_version", length: 50)]
    private ?string $serviceVersion = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ORM\ManyToMany(targetEntity: Categoria::class, mappedBy: 'fotoAstrals')]
    private Collection $categorias;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'fotoAstral', cascade: ['persist', 'remove'])]
    private Collection $reviews;


    public function __construct()
    {
        $this->categorias = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): static
    {
        $this->copyright = $copyright;
        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(string $explanation): static
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function getHdurl(): ?string
    {
        return $this->hdurl;
    }

    public function setHdurl(string $hdurl): static
    {
        $this->hdurl = $hdurl;
        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    public function getServiceVersion(): ?string
    {
        return $this->serviceVersion;
    }

    public function setServiceVersion(string $serviceVersion): static
    {
        $this->serviceVersion = $serviceVersion;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /** @return Collection<int, Categoria> */
    public function getCategorias(): Collection
    {
        return $this->categorias;
    }

    public function addCategoria(Categoria $categoria): static
    {
        if (!$this->categorias->contains($categoria)) {
            $this->categorias->add($categoria);
        }
        return $this;
    }

    public function removeCategoria(Categoria $categoria): static
    {
        $this->categorias->removeElement($categoria);
        return $this;
    }
    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setFotoAstral($this);
        }
        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getFotoAstral() === $this) {
                $review->setFotoAstral(null);
            }
        }
        return $this;
    }

}
