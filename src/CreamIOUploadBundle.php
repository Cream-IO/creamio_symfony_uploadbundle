<?php

namespace CreamIO\UploadBundle;

use CreamIO\UploadBundle\DependencyInjection\CreamIOUploadExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CreamIOUploadBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new CreamIOUploadExtension();
        }
        return $this->extension;
    }
}
