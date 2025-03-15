<?php

namespace App\Services;

class PetDataProcessor
{
    /**
     * Przetwarza dane zwierzęcia z formularza na format wymagany przez API.
     *
     * @param array $validatedData
     * @param int|null $id
     * @return array
     */
    public function process(array $validatedData, ?int $id = null): array
    {
        $tags = $this->processTags($validatedData['tags'] ?? '');
        $photoUrls = $this->processPhotoUrls($validatedData['photoUrls'] ?? '');

        $petData = [
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
            'photoUrls' => $photoUrls,
            'tags' => $tags
        ];

        if ($id !== null) {
            $petData['id'] = $id;
        }

        if (!empty($validatedData['category_name'])) {
            $petData['category'] = [
                'id' => $validatedData['category_id'] ?? 0,
                'name' => $validatedData['category_name']
            ];
        }

        return $petData;
    }

    /**
     * Przetwarza string z tagami na tablicę obiektów tagów.
     *
     * @param string $tagsString
     * @return array
     */
    private function processTags(string $tagsString): array
    {
        if (empty($tagsString)) {
            return [];
        }

        $tags = [];
        $tagNames = explode(',', $tagsString);

        foreach ($tagNames as $i => $name) {
            $tags[] = [
                'id' => $i + 1,
                'name' => trim($name)
            ];
        }

        return $tags;
    }

    /**
     * Przetwarza string z URL-ami zdjęć na tablicę URL-i.
     *
     * @param string $photoUrlsString
     * @return array
     */
    private function processPhotoUrls(string $photoUrlsString): array
    {
        if (empty($photoUrlsString)) {
            return [];
        }

        return array_map('trim', explode(',', $photoUrlsString));
    }
}
