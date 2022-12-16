<?php


namespace App\Controller;

use App\Lib\Api\Validation;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\JsonResponse;
use App\Lib\Type\Collection\InvalidTypeException;

/**
 * Abstract base for all api controllers
 */
abstract class ApiControllerBase extends AbstractController
{
    function validateTitle($title) :bool
    {
        return is_string($title) && strlen($title);
    }

    function validateDescription($description) :bool
    {
        return is_string($description) && strlen($description);
    }

    function keysValidationFailed($validKeys, $data)
    {
        $validation = new Validation($validKeys, $data);
        $result = $validation->validate();
        return array_filter($result);
    }
}
