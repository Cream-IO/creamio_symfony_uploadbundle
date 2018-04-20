<?php

namespace CreamIO\UploadBundle\Service;

use CreamIO\UploadBundle\Entity\UserStoredFile;
use CreamIO\UserBundle\Service\APIService;
use GBProd\UuidNormalizer\UuidNormalizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploaderService
{
    private $apiService;

    private $targetDirectory;

    private $validator;

    public function __construct(string $targetDirectory, APIService $apiService, ValidatorInterface $validator)
    {
        $this->apiService = $apiService;
        $this->targetDirectory = $targetDirectory;
        $this->validator = $validator;
    }

    public function generateSerializer(): Serializer
    {
        $encoders = [new JsonEncoder()];
        $objectNormalizer = new ObjectNormalizer();
        $normalizers = [new DateTimeNormalizer('d-m-Y H:i:s'), $objectNormalizer, new UuidNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }

    public function handleUpload(Request $request): UserStoredFile
    {
        $file = $request->files->get('uploaded_file');
        /** @var UploadedFile $file */
        $filename = $this->move($file);
        $postDatas = $request->request->all();
        $postDatas['file'] = $filename;
        $uploadedFile = $this->generateSerializer()->denormalize($postDatas, UserStoredFile::class);
        $this->validateEntity($uploadedFile);

        return $uploadedFile;
    }

    public function validateEntity(UserStoredFile $uploadedFile)
    {
        $validationErrors = $this->validator->validate($uploadedFile);
        if (count($validationErrors) > 0) {
            throw $this->apiService->postError($validationErrors);
        }
    }

    public function move(UploadedFile $file): string
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->getTargetDirectory(), $fileName);

        return $fileName;
    }

    public function generateUniqueFilename(): string
    {
        return md5(uniqid('creamio_', true));
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
