<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
abstract class BaseJsonController extends BaseController
{
    protected function respond(mixed $data, int $status=200): never
    {
        $this->app->setHeader('Content-Type','application/json',true);
        $this->app->setHeader('Cache-Control','no-store',true);
        http_response_code($status); echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); $this->app->close();
    }
    protected function requireAuthenticated(): void
    {
        if ($this->app->getIdentity()->guest) { $this->respond(['code'=>'authentication_required','message'=>'Authentication required'],401); }
    }
}
