<?php

namespace App\Fila\Queries;

use Illuminate\Support\Facades\File;

class PainelVideosQuery
{
    /** @var list<string> */
    private const EXTENSOES = ['mp4', 'webm', 'mov', 'm4v', 'ogv'];

    /**
     * Lista URLs públicas dos vídeos em storage/app/public/videos (acessível em /storage/videos).
     *
     * @return list<string>
     */
    public function urls(): array
    {
        $diretorio = storage_path('app/public/videos');

        if (! is_dir($diretorio)) {
            return [];
        }

        $arquivos = collect(File::files($diretorio))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::EXTENSOES, true))
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        return $arquivos
            ->map(fn ($file) => '/storage/videos/'.$file->getFilename())
            ->all();
    }
}
