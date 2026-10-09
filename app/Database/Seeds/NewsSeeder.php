<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        $tables = ['article_tags', 'bookmarks', 'contact_messages', 'activity_logs', 'login_attempts', 'newsletter_subscribers', 'articles', 'tags', 'categories', 'users', 'roles', 'site_settings'];
        foreach ($tables as $table) {
            $db->table($table)->truncate();
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        // Roles
        $roles = [
            ['id' => 1, 'name' => 'Admin',  'slug' => 'admin',  'description' => 'Full system access'],
            ['id' => 2, 'name' => 'Author', 'slug' => 'author', 'description' => 'Can manage their own articles'],
            ['id' => 3, 'name' => 'User',   'slug' => 'user',   'description' => 'Regular registered user'],
        ];
        $db->table('roles')->insertBatch($roles);

        
        // Users (admins + authors + a reader)
       
        // Passwords come from .env so the documented staff credentials are the
        // ones that actually work: security.adminPassword / security.authorPassword.
        $adminHash  = password_hash(env('security.adminPassword', 'password'), PASSWORD_BCRYPT);
        $authorHash = password_hash(env('security.authorPassword', 'password'), PASSWORD_BCRYPT);
        $userHash   = password_hash('password', PASSWORD_BCRYPT);
        $now  = date('Y-m-d H:i:s');
        $users = [
            ['id' => 1, 'role_id' => 1, 'name' => 'System Admin', 'email' => env('security.adminEmail', 'admin@newshd.com'), 'password_hash' => $adminHash, 'email_verified_at' => $now, 'bio' => 'Chief administrator of NEWSHD. Reviews and publishes author submissions.'],
            ['id' => 2, 'role_id' => 2, 'name' => 'Demo Author',  'email' => env('security.authorEmail', 'author@newshd.com'), 'password_hash' => $authorHash, 'email_verified_at' => $now, 'bio' => 'Senior reporter covering politics and technology.'],
            ['id' => 3, 'role_id' => 3, 'name' => 'Demo User',    'email' => 'user@newshd.com',    'password_hash' => $userHash, 'email_verified_at' => $now, 'bio' => 'Regular news reader.'],
            ['id' => 4, 'role_id' => 2, 'name' => 'Sita Sharma',  'email' => 'sita@newshd.com',    'password_hash' => $authorHash, 'email_verified_at' => $now, 'bio' => 'Contributing writer for sports and business desk.'],
        ];
        $db->table('users')->insertBatch($users);

        
        // Categories
        $categories = [
            ['name' => 'Politics',      'slug' => 'politics',      'description' => 'National and local political news', 'sort_order' => 1],
            ['name' => 'Sports',        'slug' => 'sports',        'description' => 'Sports coverage and highlights', 'sort_order' => 2],
            ['name' => 'Business',      'slug' => 'business',      'description' => 'Economy, markets, and business', 'sort_order' => 3],
            ['name' => 'Technology',    'slug' => 'technology',    'description' => 'Tech innovations and digital trends', 'sort_order' => 4],
            ['name' => 'Entertainment', 'slug' => 'entertainment', 'description' => 'Movies, music, and culture', 'sort_order' => 5],
            ['name' => 'World',         'slug' => 'world',         'description' => 'International news', 'sort_order' => 6],
        ];
        $db->table('categories')->insertBatch($categories);

        // Tags
        $tags = [
            ['id' => 1,  'name' => 'Election',    'slug' => 'election'],
            ['id' => 2,  'name' => 'Economy',     'slug' => 'economy'],
            ['id' => 3,  'name' => 'Football',    'slug' => 'football'],
            ['id' => 4,  'name' => 'AI',          'slug' => 'ai'],
            ['id' => 5,  'name' => 'Health',      'slug' => 'health'],
            ['id' => 6,  'name' => 'Climate',     'slug' => 'climate'],
            ['id' => 7,  'name' => 'Government',  'slug' => 'government'],
            ['id' => 8,  'name' => 'Technology',  'slug' => 'technology'],
            ['id' => 9,  'name' => 'Movies',      'slug' => 'movies'],
            ['id' => 10, 'name' => 'Finance',     'slug' => 'finance'],
            ['id' => 11, 'name' => 'Startup',     'slug' => 'startup'],
            ['id' => 12, 'name' => 'Sports',      'slug' => 'sports'],
        ];
        $db->table('tags')->insertBatch($tags);

   
        $u = 'https://images.unsplash.com/';

        $content['electoral-reform'] = <<<'HTML'
<p>After nearly 14 hours of debate that stretched past midnight, lawmakers approved a sweeping electoral reform bill early Friday — a measure supporters called the most significant overhaul of the voting system in a generation.</p>
<p>The legislation introduces independent constituency boundaries, tightens campaign-finance rules, and creates a national electronic voter register that officials say will make the ballot box both more secure and more accessible for citizens across the country.</p>
<h2>A divided chamber, a rare compromise</h2>
<p>The marathon session pitted governing party members against opposition lawmakers demanding stronger safeguards against foreign interference. After a final round of late-night amendments, the text won 148 votes in favor, with 39 abstentions.</p>
<blockquote>This is not a victory for any one party. It is a victory for the citizen who deserves to believe that every single vote counts.</blockquote>
<p><em>— the parliamentary speaker, moments after the final tally was read</em></p>
<h2>What changes for voters</h2>
<ul>
<li>Automatic registration for citizens turning 18</li>
<li>Expanded postal and early voting options</li>
<li>A hard cap on anonymous corporate donations</li>
<li>Independent, non-partisan oversight of constituency drawing</li>
</ul>
<p>The bill now moves to the president's desk for ratification. International election monitors called it "a meaningful step toward stronger democratic institutions."</p>
<p>Changes take effect at the start of the next election cycle, giving local governments time to modernize voter rolls and retrain polling staff in every province.</p>
HTML;

        $content['ai-newsroom'] = <<<'HTML'
<p>The breaking news headline was written before the reporter finished their coffee. In newsrooms around the world, artificial intelligence has moved from a novelty to a quiet, constant presence — drafting headlines, transcribing interviews, and flagging stories a human editor might miss.</p>
<p>It is a shift that has happened so gradually that many readers have not noticed. But inside the newsroom, the change is everywhere.</p>
<h2>The morning briefing, written by a machine</h2>
<p>At many outlets, the first draft of the morning's market roundup is now generated by a language model in seconds. Journalists review, correct, and publish — a workflow that has cut the time from raw data to finished story from hours to minutes.</p>
<blockquote>The best editors do not fear the machine. They use it as a junior colleague who never sleeps and never complains.</blockquote>
<p><em>— a veteran digital editor on how workflows have changed</em></p>
<h2>Where the human still matters</h2>
<ul>
<li>Investigative reporting that requires judgment and trust</li>
<li>On-the-ground interviews that no model can conduct</li>
<li>Ethical decisions about what to publish and why</li>
<li>Holding institutions to account in the public interest</li>
</ul>
<p>The industry's challenge now is transparency. Several leading outlets have begun labeling machine-assisted stories, and newsroom unions are negotiating over how automation reshapes roles.</p>
<p>What seems certain is that the newsroom of tomorrow will look very different — and that the most valuable journalists will be the ones who learn to work alongside the machine rather than against it.</p>
HTML;

        $content['champions-league'] = <<<'HTML'
<p>With the clock ticking into the 90th minute and 60,000 supporters roaring, the substitute nobody expected found the top corner. The stadium in Istanbul erupted, and a fairytale was complete: the underdogs had stunned one of Europe's richest clubs.</p>
<p>The 2–1 victory was the defining moment of a Champions League night packed with drama, a match that swung on a single, breathtaking strike from 25 yards out.</p>
<h2>A first half of patience</h2>
<p>The favorites controlled possession for long stretches, carving out chance after chance against a deep, disciplined defense. Yet the scoreboard stayed level at half-time, and the crowd sensed an upset brewing.</p>
<blockquote>We never stopped believing. The plan was to stay alive until the final whistle — and then strike once.</blockquote>
<p><em>— the winning coach, voice hoarse with emotion</em></p>
<h2>Turning points</h2>
<ol>
<li>A double save by the underdog keeper in the 67th minute</li>
<li>The decisive counter-attack launched in the 89th</li>
<li>A curling shot that clipped the crossbar on its way in</li>
</ol>
<p>The result reshapes the group standings and sends a warning to every big club in the draw: on a single night, form and budgets count for little.</p>
<p>The two sides meet again in the return fixture next month — a tie now impossible to predict.</p>
HTML;

        $content['central-bank'] = <<<'HTML'
<p>The central bank kept its benchmark interest rate unchanged on Thursday for a third straight meeting, signaling it is content to let inflation cool gradually rather than risk derailing a fragile recovery.</p>
<p>The decision, widely expected by markets, held the policy rate at its current level and reaffirmed a cautious tone in the accompanying statement.</p>
<h2>Why patience now</h2>
<p>Consumer prices have eased from last year's peaks, though core inflation remains above target. Policymakers said they wanted to see "more sustained evidence" that the decline would hold before any easing begins.</p>
<blockquote>We are not in a race to lower rates. We are in a careful walk toward price stability, and we will not stumble.</blockquote>
<p><em>— the bank's governor, in the post-meeting press conference</em></p>
<h2>What analysts are watching</h2>
<ul>
<li>Housing and services inflation, still running hot</li>
<li>The labor market, which has cooled but not cracked</li>
<li>Currency movements and imported energy prices</li>
<li>Signals from the next meeting, expected in the autumn</li>
</ul>
<p>Markets reacted calmly, with yields edging lower and equity indices holding recent gains. Most economists now expect the first rate cut in the first half of next year, assuming the data cooperates.</p>
<p>For borrowers, the message is simple: budget for rates to stay where they are a while longer.</p>
HTML;

        $content['climate-summit'] = <<<'HTML'
<p>Negotiators from nearly 190 nations reached a landmark climate agreement on Friday, ending two weeks of tense talks with a pact that many called a genuine turning point in the global fight against rising temperatures.</p>
<p>The deal commits countries to accelerate the transition away from fossil fuels, triples financing for vulnerable nations, and establishes a new framework for tracking progress every two years.</p>
<h2>Diplomacy in the final hours</h2>
<p>The hardest work happened in the last 48 hours, when a small group of ministers drafted compromise language on the most contentious issues — financing and the speed of the energy transition.</p>
<blockquote>No one got everything they wanted. That is precisely what makes this agreement real. This is what compromise at a planetary scale looks like.</blockquote>
<p><em>— the conference president, hailing the final text</em></p>
<h2>The commitments at a glance</h2>
<ul>
<li>A pledge to triple renewable capacity by 2030</li>
<li>New climate finance of $300 billion a year by 2035</li>
<li>A loss-and-damage fund now formally operational</li>
<li>Biennial transparency reviews, enforceable by peers</li>
</ul>
<p>Environmental groups gave cautious praise while warning that promises must now survive the harder test of national budgets and energy policies.</p>
<p>The real work begins at home, in the parliaments and capitals where these headline commitments must become law.</p>
HTML;

        $content['film-festival'] = <<<'HTML'
<p>The festival circuit closed this weekend with a surprise: the most talked-about film of the season was a low-budget indie drama made by first-time filmmakers, not another big-studio sequel.</p>
<p>From red-carpet premieres to quiet midnight screenings, this year's festivals delivered a vivid snapshot of where cinema is heading — and what audiences are hungry for.</p>
<h2>The breakout that stole the show</h2>
<p>A father-daughter road movie, shot on location over 40 days with a crew of twelve, took the top prize and became the festival's instant word-of-mouth hit. Distributors fought over rights within hours of its premiere.</p>
<blockquote>We made the film we could afford to make. We never imagined it would connect with so many people.</blockquote>
<p><em>— the director, accepting the grand jury award</em></p>
<h2>Three trends from this year's lineup</h2>
<ol>
<li>Audiences are embracing quieter, character-driven stories</li>
<li>Midnight genre films are drawing the biggest crowds</li>
<li>Streaming studios are competing fiercely for festival titles</li>
</ol>
<p>Box-office data from the season supports the buzz: independent releases out-performed studio expectations, a sign that theatrical audiences crave something beyond the familiar.</p>
<p>For moviegoers, the winners are clear — a slate of ambitious, original films heading to a screen near you in the coming months.</p>
HTML;

        $content['cyber-alert'] = <<<'HTML'
<p>The national cybersecurity watchdog issued a fresh alert this week, warning small businesses to harden their defenses ahead of the holiday shopping season, when attacks historically spike.</p>
<p>The advisory follows a sharp rise in reported ransomware incidents targeting retailers and service firms with fewer than 100 employees.</p>
<h2>Why small firms are in the crosshairs</h2>
<p>Attackers increasingly treat small businesses as softer targets — firms with valuable customer data but limited security budgets. "The bad guys don't care about the size of your logo," the agency's director noted in a briefing.</p>
<blockquote>A single click can undo a decade of hard work. Invest in backups before you need them, not after.</blockquote>
<p><em>— the agency's director, in this week's public briefing</em></p>
<h2>Immediate steps for business owners</h2>
<ul>
<li>Enable multi-factor authentication everywhere it is offered</li>
<li>Keep offsite, encrypted backups and test restores monthly</li>
<li>Segment payment systems from the rest of the network</li>
<li>Train staff to recognize phishing before it is too late</li>
</ul>
<p>The agency also announced a free security health-check service for businesses with fewer than 50 employees, available starting next month.</p>
<p>Officials emphasized that the goal is not to scare firms away from online sales, but to make sure they are prepared when the attempt inevitably comes.</p>
HTML;

        $content['public-transport'] = <<<'HTML'
<p>City leaders have unveiled a sweeping proposal to rebuild the public transport network, promising a faster, greener and more accessible system than anything the city has seen before.</p>
<p>The plan, which will be debated by the municipal assembly next month, calls for two new electric bus corridors, a modernized ticketing system, and dedicated lanes that would move buses past gridlocked traffic.</p>
<h2>The price of doing nothing</h2>
<p>Advocates argue the city already loses millions of working hours each year to congestion, and that the number of private vehicles has grown faster than the road network can absorb.</p>
<blockquote>Every minute spent stuck in traffic is a minute not spent with family, at work, or in rest. A functioning system is an investment, not an expense.</blockquote>
<p><em>— the mayor, presenting the plan to reporters</em></p>
<h2>What the proposal includes</h2>
<ul>
<li>Two new electric bus rapid-transit corridors</li>
<li>Unified contactless fare across buses, trams and metro</li>
<li>Priority signals that keep public vehicles moving</li>
<li>Protected bike lanes feeding every major station</li>
</ul>
<p>Funding remains the open question. The administration proposes a mix of central grants, municipal bonds, and a modest congestion charge, all to be approved by voters in a referendum this autumn.</p>
<p>If adopted, construction could begin next year, with the first corridor expected to open within three.</p>
HTML;

        $content['marathon-record'] = <<<'HTML'
<p>A 19-year-old runner from a village outside Kathmandu shattered the national marathon record on a windy, sunlit morning, finishing in a time that left even seasoned coaches searching for words.</p>
<p>Crossing the line more than three minutes inside the previous mark, she became the youngest athlete in the country's history to hold the record.</p>
<h2>A run born on mountain trails</h2>
<p>Discovered at a school-level cross-country meet, the athlete has trained for years on the steep trails above the Kathmandu Valley — conditions coaches say built the strength that carried her through the punishing final kilometers.</p>
<blockquote>Everyone expected her to run well. Nobody expected her to run like this. She negative-split the entire second half — that is rare at any age.</blockquote>
<p><em>— her coach, still shaking his head at the finish</em></p>
<h2>The numbers behind the performance</h2>
<ul>
<li>Personal best improved by more than five minutes</li>
<li>Negative splits: second half faster than the first</li>
<li>Final 10K covered at near-race-record pace</li>
<li>New national mark, pending official ratification</li>
</ul>
<p>The federation says she will now be considered for the continental championships next season, a prospect she described simply as "a dream I am not ready to wake up from."</p>
<p>For a running-mad nation, the morning felt historic — and for the teenager herself, it was just the beginning.</p>
HTML;

        $content['remote-work'] = <<<'HTML'
<p>Five years after the world went remote overnight, the office is back — but it looks nothing like it used to. And the market is quietly being rebuilt around what workers actually want.</p>
<p>Vacancy rates that soared during the pandemic have begun to stabilize in the best locations, while a wave of new, amenity-rich buildings is changing the skyline.</p>
<h2>The flight to quality</h2>
<p>Companies leasing space are choosing smaller footprints in better buildings: fewer desks, more collaboration areas, and design that treats the office as a destination rather than an obligation.</p>
<blockquote>We are not renting square meters anymore. We are renting the reason people choose to commute.</blockquote>
<p><em>— a commercial broker describing the new market logic</em></p>
<h2>Signals that reshaped the sector</h2>
<ul>
<li>Hybrid schedules are now the default for most firms</li>
<li>Flexible, short-term leases are outpacing long commitments</li>
<li>Suburbs near transit hubs are outperforming downtown cores</li>
<li>Startups are absorbing space faster than legacy tenants</li>
</ul>
<p>Economists caution that the recovery is uneven, with older buildings in secondary districts still struggling. But the direction is clear: the office has not died — it has been redesigned.</p>
<p>The next phase of the story will be written by the startups and hybrid teams reshaping where, how, and why we work.</p>
HTML;

        // ------------------------------------------------------------------
        // Articles
        // status = draft    -> author still writing (step 1)
        // status = pending  -> author submitted, awaiting admin review (step 2)
        // status = published-> admin approved & published (step 3)
        // ------------------------------------------------------------------
        $articles = [
            [
                'author_id' => 2, 'category_id' => 1, 'status' => 'published', 'is_featured' => 1, 'is_breaking' => 0,
                'title' => 'Parliament Passes Landmark Electoral Reform Bill After a Marathon Session',
                'slug' => 'parliament-electoral-reform-bill-passed',
                'excerpt' => 'Lawmakers approved the biggest overhaul of the voting system in a generation after a 14-hour session, introducing independent boundaries and a national electronic voter register.',
                'cover_image' => $u . 'photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['electoral-reform'],
                'tags' => ['Election', 'Government'],
                'view_count' => 4120, 'published_at' => '2026-08-05 09:30:00',
            ],
            [
                'author_id' => 2, 'category_id' => 4, 'status' => 'published', 'is_featured' => 0, 'is_breaking' => 1,
                'title' => 'How Artificial Intelligence Is Quietly Reshaping the Modern Newsroom',
                'slug' => 'ai-reshaping-modern-newsroom',
                'excerpt' => 'From auto-drafted headlines to instant transcription, AI has become a constant presence in newsrooms — and editors are learning to work alongside the machine.',
                'cover_image' => $u . 'photo-1518770660439-4636190af475?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['ai-newsroom'],
                'tags' => ['AI', 'Technology'],
                'view_count' => 3870, 'published_at' => '2026-08-05 14:00:00',
            ],
            [
                'author_id' => 4, 'category_id' => 2, 'status' => 'published', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'Champions League Drama: Underdogs Snatch a 90th-Minute Stunner in Istanbul',
                'slug' => 'champions-league-underdogs-stunner-istanbul',
                'excerpt' => 'A 25-yard strike deep into stoppage time completed a stunning upset as the unheralded side stunned one of Europe’s richest clubs on a night of pure drama.',
                'cover_image' => $u . 'photo-1552667466-07770ae110d0?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['champions-league'],
                'tags' => ['Football', 'Sports'],
                'view_count' => 2980, 'published_at' => '2026-08-04 11:15:00',
            ],
            [
                'author_id' => 4, 'category_id' => 3, 'status' => 'published', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'Central Bank Holds Rates Steady, Signals Patience as Inflation Cools',
                'slug' => 'central-bank-holds-rates-steady',
                'excerpt' => 'The benchmark rate stays unchanged for a third meeting, with policymakers opting for caution and signaling a first cut no earlier than next year.',
                'cover_image' => $u . 'photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['central-bank'],
                'tags' => ['Economy', 'Finance'],
                'view_count' => 2210, 'published_at' => '2026-08-04 16:45:00',
            ],
            [
                'author_id' => 2, 'category_id' => 6, 'status' => 'published', 'is_featured' => 1, 'is_breaking' => 0,
                'title' => 'Historic Climate Summit Ends With a New Pact Nations Call a Turning Point',
                'slug' => 'historic-climate-summit-new-pact',
                'excerpt' => 'Nearly 190 nations agreed to triple renewables, scale up climate finance, and overhaul how progress is tracked in a deal forged in the final hours of talks.',
                'cover_image' => $u . 'photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['climate-summit'],
                'tags' => ['Climate'],
                'view_count' => 3340, 'published_at' => '2026-08-03 10:00:00',
            ],
            [
                'author_id' => 4, 'category_id' => 5, 'status' => 'published', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'Festival Season Wrap-Up: Indie Gems, Bold Reboots and a Record Box Office',
                'slug' => 'film-festival-season-wrap-up',
                'excerpt' => 'A low-budget indie drama stole the show this festival season, while data shows audiences flocking to original, character-driven cinema.',
                'cover_image' => $u . 'photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['film-festival'],
                'tags' => ['Movies'],
                'view_count' => 1540, 'published_at' => '2026-08-03 18:20:00',
            ],
            [
                'author_id' => 2, 'category_id' => 4, 'status' => 'pending', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'Cybersecurity Watchdog Issues Fresh Alert for Small Businesses Ahead of the Holidays',
                'slug' => 'cybersecurity-alert-small-businesses',
                'excerpt' => 'A spike in ransomware attacks prompts a new advisory urging small firms to harden defenses, with a free security health-check service on the way.',
                'cover_image' => $u . 'photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['cyber-alert'],
                'tags' => ['Technology'],
                'view_count' => 0, 'published_at' => null,
            ],
            [
                'author_id' => 2, 'category_id' => 1, 'status' => 'pending', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'City Leaders Push for a Smarter, Greener Public Transport Overhaul',
                'slug' => 'city-leaders-public-transport-overhaul',
                'excerpt' => 'A sweeping proposal for electric bus corridors, unified fares and protected bike lanes heads to the municipal assembly, with funding to go to a referendum.',
                'cover_image' => $u . 'photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['public-transport'],
                'tags' => ['Government', 'Economy'],
                'view_count' => 0, 'published_at' => null,
            ],
            [
                'author_id' => 4, 'category_id' => 2, 'status' => 'pending', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'Teen Marathoner Shatters National Record on a Windy Morning in Kathmandu',
                'slug' => 'teen-marathoner-national-record',
                'excerpt' => 'A 19-year-old runner from the Kathmandu Valley stunned the field with a record run, negative-splitting the second half and shaving three minutes off the mark.',
                'cover_image' => $u . 'photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['marathon-record'],
                'tags' => ['Health', 'Sports'],
                'view_count' => 0, 'published_at' => null,
            ],
            [
                'author_id' => 4, 'category_id' => 3, 'status' => 'draft', 'is_featured' => 0, 'is_breaking' => 0,
                'title' => 'Remote Work Isn’t Fading: Inside the Quiet Rebirth of the Office Market',
                'slug' => 'remote-work-office-market-rebirth',
                'excerpt' => 'Vacancy is stabilizing and a wave of amenity-rich buildings is changing skylines as hybrid teams force a redesign of the modern office.',
                'cover_image' => $u . 'photo-1497366216548-37526070297c?auto=format&fit=crop&w=1600&q=80',
                'content' => $content['remote-work'],
                'tags' => ['Economy', 'Startup'],
                'view_count' => 0, 'published_at' => null,
            ],
        ];

        $articleIds = [];
        foreach ($articles as $row) {
            $metaTitle       = $row['title'];
            $metaDescription = mb_substr(strip_tags($row['excerpt']), 0, 160);
            $data = [
                'author_id'        => $row['author_id'],
                'category_id'      => $row['category_id'],
                'title'            => $row['title'],
                'slug'             => $row['slug'],
                'excerpt'          => $row['excerpt'],
                'content'          => $row['content'],
                'cover_image'      => $row['cover_image'],
                'status'           => $row['status'],
                'is_featured'      => $row['is_featured'],
                'is_breaking'      => $row['is_breaking'],
                'view_count'       => $row['view_count'],
                'published_at'     => $row['published_at'],
                'meta_title'       => $metaTitle,
                'meta_description' => $metaDescription,
            ];
            $db->table('articles')->insert($data);
            $newId = $db->insertID();
            $articleIds[] = $newId;
            $db->table('article_tags')->insertBatch(array_map(fn($t) => ['article_id' => $newId, 'tag_id' => $this->tagId($t, $tags)], $row['tags']));
        }

        // ------------------------------------------------------------------
        // Activity logs: demonstrate the author -> admin publish workflow.
        // For published articles the author submits (pending) then admin
        // approves (published); for pending ones the submission awaits review.
        // ------------------------------------------------------------------
        $logs = [];
        foreach ($articles as $i => $row) {
            $id = $articleIds[$i];
            if ($row['status'] === 'draft') {
                $logs[] = ['user_id' => $row['author_id'], 'action' => 'article_create', 'entity_type' => 'article', 'entity_id' => $id, 'created_at' => '2026-08-06 08:00:00'];
                continue;
            }
            $submittedAt = $row['published_at'] ? date('Y-m-d H:i:s', strtotime($row['published_at']) - 86400) : '2026-08-06 09:00:00';
            $logs[] = ['user_id' => $row['author_id'], 'action' => 'article_create', 'entity_type' => 'article', 'entity_id' => $id, 'created_at' => $submittedAt];
            if ($row['status'] === 'published') {
                $logs[] = ['user_id' => 1, 'action' => 'article_status', 'entity_type' => 'article', 'entity_id' => $id, 'created_at' => $row['published_at']];
            }
        }
        $db->table('activity_logs')->insertBatch($logs);

        // ------------------------------------------------------------------
        // Site settings
        // ------------------------------------------------------------------
        $settings = [
            ['setting_key' => 'site_name', 'setting_value' => 'NEWSHD'],
            ['setting_key' => 'site_tagline', 'setting_value' => 'देशको खबर, जनताको आवाज'],
            ['setting_key' => 'site_description', 'setting_value' => 'Trusted digital news portal delivering fast, reliable and accurate news.'],
            ['setting_key' => 'logo_url', 'setting_value' => 'assets/images/news-logo.jpg'],
            ['setting_key' => 'contact_email', 'setting_value' => 'contact@newshd.com'],
            ['setting_key' => 'contact_phone', 'setting_value' => '+977-1-1234567'],
            ['setting_key' => 'contact_address', 'setting_value' => 'Kathmandu, Nepal'],
            ['setting_key' => 'facebook_url', 'setting_value' => 'https://facebook.com/newshd'],
            ['setting_key' => 'twitter_url', 'setting_value' => 'https://twitter.com/newshd'],
            ['setting_key' => 'youtube_url', 'setting_value' => 'https://youtube.com/newshd'],
            ['setting_key' => 'theme_primary', 'setting_value' => '#d62828'],
            ['setting_key' => 'meta_keywords', 'setting_value' => 'news, politics, sports, business, technology, Nepal'],
        ];
        $db->table('site_settings')->insertBatch($settings);

        echo "News data seeded successfully: 10 articles, tags and workflow activity logs.\n";
    }

    private function tagId(string $name, array $tags): int
    {
        foreach ($tags as $tag) {
            if ($tag['name'] === $name) {
                return $tag['id'];
            }
        }
        return 0;
    }
}
