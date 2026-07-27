<?php

namespace App\Entity;

use App\Enum\ApplicationStatus;
use App\Repository\ApplicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_LISTING_FREELANCER', columns: ['listing_id', 'freelancer_id'])]
class Application
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $coverMessage = null;

    #[ORM\Column(length: 50, enumType: ApplicationStatus::class)]
    private ?ApplicationStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Listing $listing = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable:false)]
    private ?Freelancer $freelancer = null;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = ApplicationStatus::Pending;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoverMessage(): ?string
    {
        return $this->coverMessage;
    }

    public function setCoverMessage(string $coverMessage): static
    {
        $this->coverMessage = $coverMessage;

        return $this;
    }

    public function getStatus(): ?ApplicationStatus
    {
        return $this->status;
    }

    public function setStatus(ApplicationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getListing(): ?Listing
    {
        return $this->listing;
    }

    public function setListing(?Listing $listing): static
    {
        $this->listing = $listing;

        return $this;
    }

    public function getFreelancer(): ?Freelancer
    {
        return $this->freelancer;
    }

    public function setFreelancer(?Freelancer $freelancer): static
    {
        $this->freelancer = $freelancer;

        return $this;
    }
}
