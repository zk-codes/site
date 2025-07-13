<?php

class HexCell {
    public $q, $r;
    public $walls = ['E' => true, 'SE' => true, 'S' => true, 'W' => true, 'NW' => true, 'N' => true];
    public $visited = false;

    public function __construct(int $q, int $r) {
        $this->q = $q;
        $this->r = $r;
    }
}

class HexagonalMaze {
    private int $radius;
    private array $grid = [];
    private array $solutionPath = [];
    private HexCell $start_cell, $end_cell;
    private int $img_size;
    private float $hex_size, $center_x, $center_y;

    public function __construct(int $radius = 14, int $img_size = 1500) {
        $this->radius = $radius;
        $this->img_size = $img_size;
        $this->init_grid();
        $this->calculate_params();
        $this->generate_maze();
        $this->solve_maze();
    }

    private function init_grid(): void {
        for ($q = -$this->radius; $q <= $this->radius; ++$q) {
            $r1 = max(-$this->radius, -$q - $this->radius);
            $r2 = min($this->radius, -$q + $this->radius);
            for ($r = $r1; $r <= $r2; ++$r) {
                $this->grid["$q,$r"] = new HexCell($q, $r);
            }
        }
        $this->start_cell = $this->grid["0," . (-$this->radius)];
        $this->end_cell = $this->grid["0," . $this->radius];
    }

    private function calculate_params(): void {
        $margin = 50;
        $drawable = $this->img_size - ($margin * 2);
        $this->hex_size = ($drawable / (2 * $this->radius + 1)) / sqrt(3);
        $this->center_x = $this->img_size / 2;
        $this->center_y = $this->img_size / 2;
    }

    private function generate_maze(): void {
        $stack = [];
        $this->start_cell->visited = true;
        array_push($stack, $this->start_cell);
        while (!empty($stack)) {
            $current = end($stack);
            $n = $this->neighbors($current);
            if ($n) {
                $next = $n[array_rand($n)];
                $this->remove_wall($current, $next);
                $next->visited = true;
                array_push($stack, $next);
            } else {
                array_pop($stack);
            }
        }
        $this->start_cell->walls['N'] = false;
        $this->end_cell->walls['S'] = false;
    }

    private function neighbors(HexCell $cell): array {
        $dirs = ['E' => [1, 0], 'SE' => [0, 1], 'S' => [-1, 1], 'W' => [-1, 0], 'NW' => [0, -1], 'N' => [1, -1]];
        $neighbors = [];
        foreach ($dirs as [$dq, $dr]) {
            $key = ($cell->q + $dq) . "," . ($cell->r + $dr);
            $c = $this->grid[$key] ?? null;
            if ($c && !$c->visited) {
                $neighbors[] = $c;
            }
        }
        return $neighbors;
    }

    private function remove_wall(HexCell $a, HexCell $b): void {
        $dirs = ['E' => [1, 0], 'SE' => [0, 1], 'S' => [-1, 1], 'W' => [-1, 0], 'NW' => [0, -1], 'N' => [1, -1]];
        foreach ($dirs as $dir => [$dq, $dr]) {
            if ($a->q + $dq == $b->q && $a->r + $dr == $b->r) {
                $op = ['E' => 'W', 'W' => 'E', 'SE' => 'NW', 'NW' => 'SE', 'S' => 'N', 'N' => 'S'];
                $a->walls[$dir] = $b->walls[$op[$dir]] = false;
                break;
            }
        }
    }

    private function solve_maze(): void {
        foreach ($this->grid as $cell) {
            $cell->visited = false;
        }
        $this->solutionPath = [];
        $this->dfs($this->start_cell, $this->solutionPath);
    }
    
    private function dfs(HexCell $cell, array &$path): bool {
        $cell->visited = true;
        $path[] = $cell;
        if ($cell === $this->end_cell) {
            return true;
        }
        foreach ($this->open_neighbors($cell) as $n) {
            if (!$n->visited) {
                if ($this->dfs($n, $path)) {
                    return true;
                }
            }
        }
        array_pop($path); // Backtrack
        return false;
    }

    private function open_neighbors(HexCell $c): array {
        $dirs = ['E' => [1, 0], 'SE' => [0, 1], 'S' => [-1, 1], 'W' => [-1, 0], 'NW' => [0, -1], 'N' => [1, -1]];
        $res = [];
        foreach ($dirs as $dir => [$dq, $dr]) {
            if (!$c->walls[$dir]) {
                $key = ($c->q + $dq) . "," . ($c->r + $dr);
                $n = $this->grid[$key] ?? null;
                if ($n) {
                    $res[] = $n;
                }
            }
        }
        return $res;
    }
    
    private function hex_to_pixel(HexCell $c): array {
        $x = $this->center_x + $this->hex_size * (sqrt(3) * $c->q + sqrt(3) / 2 * $c->r);
        $y = $this->center_y + $this->hex_size * (3.0 / 2.0 * $c->r);
        return ['x' => $x, 'y' => $y];
    }
    
    private function get_image(bool $solution): string {
        ob_start();
        $this->draw_image($solution);
        $data = ob_get_clean();
        return 'data:image/png;base64,' . base64_encode($data);
    }

    public function puzzle_src(): string {
        return $this->get_image(false);
    }

    public function solution_src(): string {
        return $this->get_image(true);
    }

    private function draw_image(bool $solution): void {
        $img = imagecreatetruecolor($this->img_size, $this->img_size);
        $bg = imagecolorallocate($img, 255, 255, 255);
        $wall_color = imagecolorallocate($img, 0, 0, 0);
        $solution_color = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $bg);
        imagesetthickness($img, 2);
    
        // Draw the maze walls
        foreach ($this->grid as $cell) {
            $center = $this->hex_to_pixel($cell);
            $wall_keys = array_keys($cell->walls);
            for ($i = 0; $i < 6; $i++) {
                $wall_name = $wall_keys[$i];
                if ($cell->walls[$wall_name]) {
                    // Angles for pointy-topped hexagons
                    $angle1 = deg2rad(60 * $i - 30);
                    $angle2 = deg2rad(60 * ($i + 1) - 30);
                    $x1 = $center['x'] + $this->hex_size * cos($angle1);
                    $y1 = $center['y'] + $this->hex_size * sin($angle1);
                    $x2 = $center['x'] + $this->hex_size * cos($angle2);
                    $y2 = $center['y'] + $this->hex_size * sin($angle2);
                    imageline($img, (int)$x1, (int)$y1, (int)$x2, (int)$y2, $wall_color);
                }
            }
        }
    
        if ($solution && !empty($this->solutionPath)) {
            imagesetthickness($img, 4);
            for ($i = 0; $i < count($this->solutionPath) - 1; $i++) {
                $p1 = $this->hex_to_pixel($this->solutionPath[$i]);
                $p2 = $this->hex_to_pixel($this->solutionPath[$i + 1]);
                imageline($img, (int)$p1['x'], (int)$p1['y'], (int)$p2['x'], (int)$p2['y'], $solution_color);
            }
        }

        $arrow_color = $wall_color;
        $arrow_base_size = $this->hex_size * 0.6;
        $arrow_height = $this->hex_size * 0.6;
        $offset_multiplier = 0.4;

        $start_pos = $this->hex_to_pixel($this->start_cell);
        $angle1_rad = deg2rad(270);
        $angle2_rad = deg2rad(330);
        $x1 = $start_pos['x'] + $this->hex_size * cos($angle1_rad);
        $y1 = $start_pos['y'] + $this->hex_size * sin($angle1_rad);
        $x2 = $start_pos['x'] + $this->hex_size * cos($angle2_rad);
        $y2 = $start_pos['y'] + $this->hex_size * sin($angle2_rad);
        $mid_x = ($x1 + $x2) / 2;
        $mid_y = ($y1 + $y2) / 2;
        $offset = $this->hex_size * $offset_multiplier;

        $entrance_arrow_points = [
            (int)($mid_x - $arrow_base_size / 2), (int)($mid_y - $offset - $arrow_height),
            (int)($mid_x + $arrow_base_size / 2), (int)($mid_y - $offset - $arrow_height),
            (int)($mid_x), (int)($mid_y - $offset)
        ];
        imagefilledpolygon($img, $entrance_arrow_points, 3, $arrow_color);

        $end_pos = $this->hex_to_pixel($this->end_cell);
        $angle1_rad = deg2rad(90);
        $angle2_rad = deg2rad(150);
        $x1 = $end_pos['x'] + $this->hex_size * cos($angle1_rad);
        $y1 = $end_pos['y'] + $this->hex_size * sin($angle1_rad);
        $x2 = $end_pos['x'] + $this->hex_size * cos($angle2_rad);
        $y2 = $end_pos['y'] + $this->hex_size * sin($angle2_rad);
        $mid_x = ($x1 + $x2) / 2;
        $mid_y = ($y1 + $y2) / 2;
        $offset = $this->hex_size * $offset_multiplier;

        $exit_arrow_points = [
            (int)($mid_x - $arrow_base_size / 2), (int)($mid_y + $offset),
            (int)($mid_x + $arrow_base_size / 2), (int)($mid_y + $offset),
            (int)($mid_x), (int)($mid_y + $offset + $arrow_height)
        ];
        imagefilledpolygon($img, $exit_arrow_points, 3, $arrow_color);
            
        imagepng($img);
        imagedestroy($img);
    }
}

$mazeGenerated = false;
$puzzleSrc = '';
$solutionSrc = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maze = new HexagonalMaze(14, 1500);
    $puzzleSrc = $maze->puzzle_src();
    $solutionSrc = $maze->solution_src();
    $mazeGenerated = true;
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <title>Hexagonal Maze Maker | Zachary Kai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/assets/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="/assets/styles/print.css" media="print">
    <link href="/assets/manifest.json" rel="manifest"/>
    <link rel="alternate" type="application/rss+xml" title="Zachary Kai" href="/assets/rss.xml">
    <link rel="webmention" href="https://webmention.io/zacharykai.net/webmention" />
    <link rel="canonical" href="https://zacharykai.net/tools/hexagonmaze">
    <meta name="date" content="2025-07-09">
    <meta name="last-modified" content="2025-07-09">
    <meta name="description" content="Making a hexagonal maze and its solution.">
</head>
<body>
    <p><a href="#top" class="essentials">Begin reading...</a></p>
    <header><nav><a href="/">Zachary Kai</a></nav></header>
    <main class="h-entry e-content">
        <header>
            <p class="breadcrumbs"><a href="/">Homepage</a> • <a href="/sitemap#tools">Tools</a></p>
            <h1 class="p-name">Hexagonal Maze Maker</h1>
            <p class="postmeta">
                <strong>Published</strong>: <time class="dt-published" datetime="2025-07-09">9 Jul 2025</time> | 
                <strong>Updated</strong>: <time class="dt-modified" datetime="2025-07-09">9 Jul 2025</time>
            </p>
        </header>
        <p id="top" class="p-summary">Click the button to create a random hexagonal maze and its solution!</p>
        <h2 id="generator">Generator</h2>
        <form action="/tools/hexagonmaze" method="post">
            <button type="submit">Generate Maze</button>
        </form>

        <?php if ($mazeGenerated): ?>
        <h2 id="puzzle">Puzzle</h2>
        <p>Happy puzzling! You can right-click or long-press on the image to save it.</p>
        <img src="<?php echo $puzzleSrc; ?>" alt="A hexagonal maze puzzle.">
        <p><a href="<?php echo $puzzleSrc; ?>" download="hexagonal_maze_puzzle.png"><strong>Download Puzzle PNG</strong></a></p>

        <details style="margin-top: 2em;">
            <summary><h2 id="solution" style="display: inline; font-size: 1.2em; cursor: pointer;">Solution</h2></summary>
            <img src="<?php echo $solutionSrc; ?>" alt="The solution path for the hexagonal maze.">
            <p><a href="<?php echo $solutionSrc; ?>" download="hexagonal_maze_solution.png"><strong>Download Solution PNG</strong></a></p>
        </details>
        <?php endif; ?>
        
<p>•--♡--•</p>
            <section class="essentials">
                <p><strong>Copy & Share</strong>: <a href="/" class="u-url">zacharykai.net/</a></p>
                <p><strong>Statistics</strong> &rarr; Word Count: 44 | Reading Time: 0:13</p>
                <hr>
                <p>
                    <strong>Enjoyed This? Support What I Do:</strong>
                    <a href="/paypal" rel="noopener">PayPal</a> |
                    <a href="/stripe" rel="noopener">Stripe</a>
                </p>
                <hr>
                <p>
                    <strong>Reply Via</strong>:
                    <a href="/contact">Email</a> | 
                    <a href="/guestbook">Guestbook</a> |
                    <a href="/unoffice-hours">UnOffice Hours</a> | 
                    <a href="/webmention" rel="noopener">Webmention</a>
                </p>
                <p>
                    <strong>Found An Error?</strong>
                    <a href="/contact" rel="noopener">Suggest An Edit</a> |
                    <a href="/source" rel="noopener">View Source Code</a>
                </p>
            </section>
        </main>
        <section class="h-card vcard">
            <section class="h-card-image">
                <picture>
                    <source srcset="/assets/zk_icon.webp" type="image/webp">
                    <img class="u-photo" loading="lazy" src="/assets/zk_icon.png" alt="Zachary Kai's digital drawing: 5 stacked books (blue/teal/green/purple, black spine designs), green plant behind top book, purple heart on either side.">
                </picture>
            </section>
            <section class="h-card-content">
                <p><strong><a class="u-url u-id p-name" href="https://zacharykai.net" rel="me"><span class="fn">Zachary Kai</span></a></strong> — <span class="p-pronouns">he/him</span> | <a class="u-email email" href="mailto:hi@zacharykai.net" rel="me">hi@zacharykai.net</a></p>
                <p class="p-note">Zachary Kai is a space fantasy writer, offbeat queer, traveler, zinester, and avowed generalist. The internet is his livelihood and lifeline.</p>
            </section>
        </section>
        <section class="acknowledgement">
            <h2>Acknowledgement Of Country</h2>
            <p>I acknowledge the folks whose lands I owe my existence to: the Koori people. The traditional owners, storytellers, and first peoples. This land's been tended and lived alongside for millennia with knowledge passed down through generations. What a legacy. May it prevail.</p>
        </section>
        <p><a href="#top" class="essentials">Read again...</a></p>
        <footer>
            <p>Est. 2024 || 
                <a href="/about">About</a> | 
                <a href="/colophon">Accessibility & Colophon</a> | 
                <a href="/changelog">Changelog</a> | 
                <a href="/cv">CV</a> | 
                <a href="/hello">Contact</a> | 
                <a href="/newsletter">Newsletter</a> | 
                <a href="/random">Random</a> | 
                <a href="/assets/rss.xml">RSS</a> |  
                <a href="/sitemap">Sitemap</a>
            </p>
            <p class="elsewhere">Elsewhere || 
                <a href="/github" rel="noopener">Github</a> | 
                <a href="/indieweb" rel="noopener">Indieweb</a> | 
                <a href="/internet-archive" rel="noopener">Internet Archive</a> | 
                <a href="/linkedin" rel="noopener">Linkedin</a></p>
        </footer>
    </body>
</html>