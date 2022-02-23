<?php

namespace App\Model\Account;

use ArrayObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Elao\Enum\Bridge\Symfony\Serializer\Normalizer\EnumNormalizer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializableTrait
{
    /**
     * @inheritDoc
     * @throws ExceptionInterface
     */
    public function jsonSerialize(): float|int|bool|ArrayObject|array|string|null
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = [
            new EnumNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ];

        $serializer = new Serializer($normalizer);

        return $serializer->normalize($this, null, ['groups' => 'serialize']);
    }
}