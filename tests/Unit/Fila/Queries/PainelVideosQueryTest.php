<?php

namespace Tests\Unit\Fila\Queries;

use App\Fila\Queries\PainelVideosQuery;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PainelVideosQueryTest extends TestCase
{
    #[Test]
    public function lista_videos_da_pasta_storage_videos_em_ordem_alfabetica(): void
    {
        $diretorio = storage_path('app/public/videos');
        File::ensureDirectoryExists($diretorio);

        $prefixo = 'test-playlist-'.uniqid().'-';
        File::put($diretorio.'/'.$prefixo.'b.mp4', 'fake');
        File::put($diretorio.'/'.$prefixo.'a.mp4', 'fake');
        File::put($diretorio.'/'.$prefixo.'ignore.txt', 'fake');

        try {
            $urls = collect(app(PainelVideosQuery::class)->urls())
                ->filter(fn (string $url) => str_contains($url, $prefixo))
                ->values()
                ->all();

            $this->assertCount(2, $urls);
            $this->assertSame('/storage/videos/'.$prefixo.'a.mp4', $urls[0]);
            $this->assertSame('/storage/videos/'.$prefixo.'b.mp4', $urls[1]);
        } finally {
            File::delete($diretorio.'/'.$prefixo.'a.mp4');
            File::delete($diretorio.'/'.$prefixo.'b.mp4');
            File::delete($diretorio.'/'.$prefixo.'ignore.txt');
        }
    }
}
