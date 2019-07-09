<?php

namespace App\Tests\Twig;

use PHPUnit\Framework\TestCase;
use App\Twig\AppExtension; // this is the file which i am going to test

class SluggerTest extends TestCase
{
    //static method
    // public function testSlugify()
    // {
    //     $slugger = new AppExtension;
    //     $this->assertSame('cell-phones', $slugger->slugify('Cell Phones'));
    // }

    
    
    //dynamic method
    /**
     * @dataProvider getSlugs
     */
    public function testSlugify(string $string, string $slug)
    {
        $slugger = new AppExtension;
        $this->assertSame('$slug', $slugger->slugify('$string'));
    }

    public function getSlugs()
    {
        return [
            ['Lorem Ipsum', 'lorem-ipsum'],
            [' Lorem Ipsum', 'lorem-ipsum'],
        ];
    }

}
