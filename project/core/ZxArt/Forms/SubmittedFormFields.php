<?php

declare(strict_types=1);

namespace ZxArt\Forms;

/**
 * Reshapes a submitted creation form into the structure the CMS data chunks
 * expect, mirroring `controller::parseFormData()` for the `/formdata/` endpoint.
 *
 * PHP groups uploads by property (`name`, `tmp_name`, …) with the field name
 * inside. Single-file chunks want exactly that per field, but a multi-file
 * selector ({@see \filesDataChunk}) wants one array per uploaded file, so those
 * are transposed by file index.
 */
final readonly class SubmittedFormFields
{
    /**
     * @param array<string, array<string, mixed>> $files `$_FILES['fields']`
     * @param array<string, mixed> $post `$_POST['fields']`
     * @return array<string, mixed>
     */
    public static function merge(array $files, array $post): array
    {
        $fields = [];
        foreach ($files as $fileProperty => $fieldValues) {
            if (!is_array($fieldValues)) {
                continue;
            }
            foreach ($fieldValues as $fieldName => $fieldValue) {
                $fieldKey = (string)$fieldName;
                if (is_array($fieldValue)) {
                    foreach ($fieldValue as $fileIndex => $value) {
                        $fields[$fieldKey][$fileIndex][(string)$fileProperty] = $value;
                    }
                } else {
                    $fields[$fieldKey][(string)$fileProperty] = $fieldValue;
                }
            }
        }

        foreach ($post as $fieldName => $fieldValue) {
            $fieldKey = (string)$fieldName;
            if (isset($fields[$fieldKey]) && is_array($fields[$fieldKey]) && is_array($fieldValue)) {
                $fields[$fieldKey] = array_merge($fields[$fieldKey], $fieldValue);
            } else {
                $fields[$fieldKey] = $fieldValue;
            }
        }

        return $fields;
    }
}
