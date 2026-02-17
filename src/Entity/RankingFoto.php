<?php

namespace App\Entity;

use App\Repository\RankingFotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingFotoRepository::class)]
#[ORM\Table(name: 'ranking_foto', schema: 'apodnasa')]
class RankingFoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FotoAstral::class)]
    #[ORM\JoinColumn(name: 'id_foto_astral', referencedColumnName: 'id')]
    private ?FotoAstral $fotoAstral = null;

    #[ORM\ManyToOne(targetEntity: Ranking::class, inversedBy: 'rankingFotos')]
    #[ORM\JoinColumn(name: 'id_ranking', referencedColumnName: 'id')]
    private ?Ranking $ranking = null;

    #[ORM\Column(name: "posicion", type: 'integer')]
    private ?int $posicion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFotoAstral(): ?FotoAstral
    {
        return $this->fotoAstral;
    }

    public function setFotoAstral(?FotoAstral $fotoAstral): static
    {
        $this->fotoAstral = $fotoAstral;
        return $this;
    }

    public function getRanking(): ?Ranking
    {
        return $this->ranking;
    }

    public function setRanking(?Ranking $ranking): static
    {
        $this->ranking = $ranking;
        return $this;
    }

    public function getPosicion(): ?int
    {
        return $this->posicion;
    }

    public function setPosicion(int $posicion): static
    {
        $this->posicion = $posicion;
        return $this;
    }
}
