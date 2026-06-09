<?php

namespace Dtc\GridBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dtc\GridBundle\Annotation\Column as GridColumn;
use Dtc\GridBundle\Annotation\Grid;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
#[Grid]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[GridColumn(label: 'Product Name', sortable: true, searchable: true)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[GridColumn(label: 'Category', sortable: true, searchable: true)]
    private string $category = '';

    #[ORM\Column(type: 'float')]
    #[GridColumn(label: 'Price', sortable: true)]
    private float $price = 0.0;

    #[ORM\Column(type: 'string', length: 50)]
    #[GridColumn(label: 'Status', sortable: true, searchable: true)]
    private string $status = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
