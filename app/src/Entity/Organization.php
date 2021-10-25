<?php

declare(strict_types = 1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use function array_unique;
use function json_encode;
use const SORT_REGULAR;

/**
 * Class Organization
 * @package App\Entity
 * @author  Kristo Leas <kristo.leas@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="organizations")
 */
class Organization {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="bigint")
	 */
	private ?int $id = null;
	
	/**
	 * @ORM\Column(type="string", length=150, nullable=false)
	 */
	private string $name;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Organization")
	 * @ORM\JoinTable(name="parents")
	 */
	private Collection $parents;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Organization")
	 * @ORM\JoinTable(name="daughters")
	 */
	private Collection $daughters;
	
	public function __construct(string $name) {
		$this->name      = $name;
		$this->parents   = new ArrayCollection();
		$this->daughters = new ArrayCollection();
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function setName(string $name): Organization {
		$this->name = $name;
		return $this;
	}
	
	public function getParents(): Collection {
		return $this->parents;
	}
	
	public function addParent(Organization $parent): Organization {
		if ($this->parents->contains($parent) === false) {
			$this->parents->add($parent);
			$parent->addDaughter($this);
		}
		return $this;
	}
	
	public function getDaughters(): Collection {
		return $this->daughters;
	}
	
	public function addDaughter(Organization $daughter): Organization {
		if ($this->daughters->contains($daughter) === false) {
			$this->daughters->add($daughter);
			$daughter->addParent($this);
		}
		return $this;
	}
	
	public function __toString(): string {
		return json_encode($this->__toArray());
	}
	
	public function __toArray(): array {
		$sisters   = [...$this->getSistersTree($this)];
		$daughters = [];
		foreach ($this->daughters as $daughter) {
			$daughters[] = ['relationship_type' => 'daughter', 'org_name' => $daughter->getName()];
		}
		$parents = [...$this->getParentsTree($this)];
		return [...$parents, ...$sisters, ...$daughters];
	}
	
	private function getParentsTree(Organization $organization): array {
		$parents = [];
		foreach ($organization->getParents() as $parent) {
			$parents[] = ['relationship_type' => 'parent', 'org_name' => $parent->getName()];
		}
		return $parents;
	}
	
	private function getSistersTree(Organization $organization): array {
		$sisters = [];
		foreach ($organization->getParents() as $parent) {
			if ($parent->getDaughters()->count() > 0) {
				foreach ($parent->getDaughters() as $daughter) {
					if ($daughter->getName() !== $organization->getName()) {
						$sisters[] = $formatted[] = ['relationship_type' => 'sister', 'org_name' => $daughter->getName()];
					}
				}
			}
		}
		return array_unique($sisters, SORT_REGULAR);
	}
}