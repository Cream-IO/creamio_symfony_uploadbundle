<?php

namespace CreamIO\UploadBundle\Service;

use CreamIO\UploadBundle\Model\UserStoredFile;
use CreamIO\BaseBundle\Exceptions\APIException;
use CreamIO\BaseBundle\Exceptions\APIError;
use CreamIO\BaseBundle\Service\APIService;
use GBProd\UuidNormalizer\UuidNormalizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UploaderService.
 */
class UploaderService
{
    /**
     * Returned error title
     */
    const BAD_CLASSNAME_ERROR = "The classname provided to generate uploaded file does not extends UserStoredFile.";

    /**
     * @var APIService Injected API service
     */
    private $apiService;

    /**
     * @var string Injected upload directory from config
     */
    private $targetDirectory;

    /**
     * @var ValidatorInterface Injected validator service
     */
    private $validator;

    /**
     * @var string Injected default file upload entity from config
     */
    private $defaultClassToGenerate;

    /**
     * @var string Injected default file field for default file upload entity
     */
    private $defaultClassFileField;

    /**
     * UploaderService constructor.
     *
     * @param string             $targetDirectory        Target upload directory, injected from config file
     * @param string             $defaultClassToGenerate Classname to generate by default if not provided in handleUpload method. Example : "App\Entity\GalleryImage"
     * @param string             $defaultClassFileField  Field in the file upload entity that contain the file name by default if not provided in handleUpload
     * @param APIService         $apiService             Injected API service from base bundle
     * @param ValidatorInterface $validator              Injected validator service
     */
    public function __construct(string $targetDirectory, string $defaultClassToGenerate, string $defaultClassFileField, APIService $apiService, ValidatorInterface $validator)
    {
        if (false === is_subclass_of($defaultClassToGenerate, 'CreamIO\UploadBundle\Model\UserStoredFile')) {
            $APIError = new APIError(Response::HTTP_INTERNAL_SERVER_ERROR, SELF::BAD_CLASSNAME_ERROR);

            throw new APIException($APIError);
        }
        $this->apiService = $apiService;
        $this->targetDirectory = $targetDirectory;
        $this->validator = $validator;
        $this->defaultClassToGenerate = $defaultClassToGenerate;
        $this->defaultClassFileField = $defaultClassFileField;
    }

    /**
     * Generates a serializer to denormalize file upload entities.
     *
     * @return Serializer
     */
    public function generateSerializer(): Serializer
    {
        $encoders = [new JsonEncoder()];
        $objectNormalizer = new ObjectNormalizer();
        $normalizers = [new DateTimeNormalizer('d-m-Y H:i:s'), $objectNormalizer, new UuidNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }

    /**
     * Main upload handling method, moves the file and generate the file upload entity.
     *
     * @param Request     $request          Handled HTTP request
     * @param bool        $validate         Validate or not the entity during upload processing ? Useful when you need to add some parameters to the entity before validation
     * @param null|string $classToGenerate  Classname to generate. Example : "App\Entity\GalleryImage"
     * @param null|string $fileField        Field in the file upload entity that contain the file name
     *
     * @return UserStoredFile File upload entity
     */
    public function handleUpload(Request $request, bool $validate = true, ?string $classToGenerate = null, ?string $fileField = null): UserStoredFile
    {
        if (null === $fileField) {
            $fileField = $this->defaultClassFileField;
        }
        if (null === $classToGenerate) {
            $classToGenerate = $this->defaultClassToGenerate;
        }
        $file = $request->files->get('uploaded_file');
        /** @var UploadedFile $file */
        $filename = $this->move($file);
        $uploadedFile = $this->denormalizeEntity($request, $classToGenerate, $fileField, $filename);
        if ($validate) {
            $this->validateEntity($uploadedFile);
        }

        return $uploadedFile;
    }

    /**
     * Generate a file upload entity.
     *
     * @param Request $request          Handled HTTP request
     * @param string  $classToGenerate  Classname to generate. Example : "App\Entity\GalleryImage"
     * @param string  $fileField        Field in your entity that contain the file name
     * @param string  $filename         Filename to put in file field
     *
     * @return UserStoredFile File upload entity
     */
    public function denormalizeEntity(Request $request, string $classToGenerate, string $fileField, string $filename): UserStoredFile
    {
        $postDatas = $request->request->all();
        $postDatas[$fileField] = $filename;

        return $this->generateSerializer()->denormalize($postDatas, $classToGenerate);
    }

    /**
     * Validates the file upload entity.
     *
     * @param UserStoredFile $uploadedFile
     *
     * @throws APIException If validation failed, contains violations list
     */
    public function validateEntity(UserStoredFile $uploadedFile)
    {
        $validationErrors = $this->validator->validate($uploadedFile);
        if (count($validationErrors) > 0) {
            throw $this->apiService->postError($validationErrors);
        }
    }

    /**
     * Move the uploaded file to the upload directory.
     *
     * @param UploadedFile $file
     *
     * @return string Filename
     */
    public function move(UploadedFile $file): string
    {
        $fileName = $this->generateUniqueFilename($file->guessExtension());
        $file->move($this->getTargetDirectory(), $fileName);

        return $fileName;
    }

    /**
     * Generated a md5 filename based on uniqid.
     *
     * @param null|string $fileExtension
     *
     * @return string
     */
    public function generateUniqueFilename(?string $fileExtension): string
    {
        $uniqueName = md5(uniqid());
        if(null !== $fileExtension) {
            $uniqueName = sprintf('%s.%s', $uniqueName, $fileExtension);
        }

        return $uniqueName;
    }

    /**
     * Returns the upload directory.
     *
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
