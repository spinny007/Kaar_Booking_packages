<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Document;
defined('_JEXEC') or die;
interface PrivateDocumentStorageInterface
{
    public function store(string $ownerScope, string $binary, string $mimeType): array;
    public function openInline(string $key): iterable;
    public function delete(string $key): void;
}
