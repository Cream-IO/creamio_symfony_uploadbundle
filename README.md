# CreamIO Symfony Upload Bundle

Service to handle upload built over [Symfony 4.0][3].

Requirements
------------

  * Symfony 4;
  * PHP 7.2 or higher;
  * Composer;
  * MySQL database;
  * PDO PHP extension;
  * qraimbault/creamio_symfony_basebundle (included in require);
  * and the [usual Symfony application requirements][1].
  
Installation
------------

Require the bundle from a symfony 4 application.

Make the base bundle configuration according to the [documentation](https://github.com/Cream-IO/symfony_basebundle/blob/master/README.md).

Create an entity implementing CreamIO\UploadBundle\Model\UserStoredFile. Example:

```php
namespace App\Entity;

use CreamIO\UploadBundle\Model\UserStoredFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AvatarRepository")
 * @ORM\Table(name="myapp_user_avatar")
 */
class Avatar extends UserStoredFile
{
    /**
     * @ORM\OneToOne(targetEntity="CreamIO\UserBundle\Entity\BUser")
     * @ORM\JoinColumn(name="user_related_id", nullable=false)
     *
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return Avatar
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }
}

```

Add the configuration for the bundle to your application by adding `config/packages/creamio_upload.yaml`:

```yaml
creamio_upload:
    upload_directory: '%kernel.project_dir%/public/uploads'
    default_upload_file_class: 'App\Entity\Avatar'
    default_upload_file_field: 'file'
```

Usage
-----

Create a controller handling uploads. Example:

```php
namespace App\Controller;

use App\Entity\Avatar;
use CreamIO\UploadBundle\Service\UploaderService;
use CreamIO\UserBundle\Entity\BUser;
use CreamIO\BaseBundle\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UploadedFileController.
 *
 * @Route("/file-upload")
 */
class UploadedFileController extends Controller
{
    /**
     * @Route("", methods="POST")
     */
    public function upload(Request $request, UploaderService $uploader, APIService $apiService): Response
    {
        /** @var Avatar $uploadedFile */
        $uploadedFile = $uploader->handleUpload($request, false);
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(BUser::class)->find('d21406db-1213-494a-a69b-bc9b3a4674d6');
        $uploadedFile->setUser($user);
        $uploader->validateEntity($uploadedFile);
        $em->persist($uploadedFile);
        $em->flush();

        return $apiService->successWithoutResults($uploadedFile->getId(), Response::HTTP_OK, $request);
    }

    /**
     * @Route("", methods="GET")
     */
    public function formUpload(Request $request): Response
    {
        return $this->render('uploaded_file/index.html.twig');
    }
}
```

You can specify some parameters to the handleUpload method, here is the function signature:
```php
    /**
     * Main upload handling method, moves the file and generate the file upload entity.
     *
     * @param Request     $request          Handled HTTP request
     * @param bool        $validate         Validate or not the entity during upload processing ? Useful when you need to add some parameters to the entity before validation
     * @param null|string $classToGenerate  Classname to generate. Example : "App\Entity\GalleryImage" or GalleryImage::class
     * @param null|string $fileProperty     Property in the file upload entity that contain the file name
     *
     * @return UserStoredFile File upload entity
     */
    public function handleUpload(Request $request, bool $validate = true, ?string $classToGenerate = null, ?string $fileProperty = null): UserStoredFile
```

Project tree
------------

```bash
.
└── src
    ├── DependencyInjection
    ├── Model               # Doctrine UserStoredFile mapped super class model
    ├── Resources
    │   └── config          # Services injection and config file
    └── Service             # Upload handling service
```

License
-------
[![Creative Commons License](https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png)](http://creativecommons.org/licenses/by-nc-sa/4.0/)

This software is distributed under the terms of the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International Public License. License is described below, you can find a human-readable summary of (and not a substitute for) the license [here](http://creativecommons.org/licenses/by-nc-sa/4.0/).


[1]: https://symfony.com/doc/current/reference/requirements.html
[2]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[3]: https://symfony.com/