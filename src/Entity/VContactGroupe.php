<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: "v_contactgroupe")] // <-- mets ici le nom exact de ta vue MySQL
class VContactGroupe
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $postnom = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: "string", length: 150, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(name: "groupe", type: "string", length: 150, nullable: true)]
    private ?string $groupe = null;

    #[ORM\Column(type: "integer")]
    private ?int $user = null;

    // Getters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function getPostnom(): ?string
    {
        return $this->postnom;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function getGroupe(): ?string
    {
        return $this->groupe;
    }

    public function getUser(): ?int
    {
        return $this->user;
    }
    #select `c`.`id` AS `id`,`c`.`telephone` AS `telephone`,`c`.`nom` AS `nom`,`c`.`postnom` AS `postnom`,`c`.`adresse` AS `adresse`,`c`.`fonction` AS `fonction`,`g`.`designation` AS `groupe`,`c`.`user_id` AS `user` from ((`db_cgssms`.`contact` `c` left join `db_cgssms`.`contact_groupe` `cg` on(`c`.`id` = `cg`.`contact_id`)) left join `db_cgssms`.`groupe` `g` on(`g`.`id` = `cg`.`groupe_id`))

}
