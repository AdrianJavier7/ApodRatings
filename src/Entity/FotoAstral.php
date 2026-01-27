<?php

namespace App\Entity;

use App\Repository\FotoAstralRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FotoAstralRepository::class)]
class FotoAstral
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "date", type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'fotoAstral')]
    private Collection $reviews;

    /**
     * @var Collection<int, Categoria>
     */
    #[ORM\ManyToMany(targetEntity: Categoria::class, inversedBy: 'fotoAstrals')]
    private Collection $categorias;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $copyright = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $explanation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hdurl = null;

    #[ORM\Column(length: 100)]
    private ?string $mediaType = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->categorias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
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
            // set the owning side to null (unless already changed)
            if ($review->getFotoAstral() === $this) {
                $review->setFotoAstral(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Categoria>
     */
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
}
