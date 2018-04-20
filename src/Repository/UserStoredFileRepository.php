<?php

namespace CreamIO\UploadBundle\Repository;

use CreamIO\UploadBundle\Entity\UserStoredFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserStoredFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStoredFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStoredFile[]    findAll()
 * @method UserStoredFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStoredFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserStoredFile::class);
    }
}
