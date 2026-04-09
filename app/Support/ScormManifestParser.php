<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;

class ScormManifestParser
{
    public static function parse(string $manifestXml): array
    {
        libxml_use_internal_errors(true);
        $manifest = simplexml_load_string($manifestXml);

        if (! $manifest instanceof SimpleXMLElement) {
            throw new RuntimeException('SCORM manifest could not be parsed.');
        }

        $namespaces = $manifest->getNamespaces(true);
        if (isset($namespaces[''])) {
            $manifest->registerXPathNamespace('ims', $namespaces['']);
        }

        $title = trim((string) ($manifest->organizations->organization->title ?? $manifest->metadata->title ?? $manifest['identifier']));
        $resources = $manifest->xpath('//ims:resource') ?: $manifest->xpath('//resource') ?: [];

        $launchPath = null;
        $resourceIdentifiers = [];

        foreach ($resources as $resource) {
            $identifier = trim((string) ($resource['identifier'] ?? ''));
            $href = trim((string) ($resource['href'] ?? ''));

            if ($identifier !== '') {
                $resourceIdentifiers[] = $identifier;
            }

            if ($launchPath === null && $href !== '') {
                $launchPath = ltrim(str_replace('\\', '/', $href), '/');
            }
        }

        if ($launchPath === null) {
            throw new RuntimeException('SCORM manifest does not contain a launchable resource.');
        }

        return [
            'title' => $title !== '' ? $title : 'SCORM Package',
            'launch_path' => $launchPath,
            'resource_identifiers' => $resourceIdentifiers,
        ];
    }
}
