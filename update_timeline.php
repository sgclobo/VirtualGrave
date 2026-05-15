<?php
/**
 * One-time script — rebuild timeline to match biography content.
 * DELETE after running.
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';

$db = getDB();

// Clear existing timeline data
$db->exec('DELETE FROM timeline');
$db->exec('ALTER TABLE timeline AUTO_INCREMENT = 1');

$events = [
    [
        'year'       => 1953,
        'month'      => 10,
        'title'      => 'Birth & Childhood',
        'description'=> 'Hércio Maria das Neves Campos was born on October 25, 1953, in Laclubar, son of Manuel Ângelo Pires de Oliveira Campos and Edviges Sequeira. He grew up in Dili, known from childhood for being responsible, hardworking, curious, and respectful.',
        'icon'       => 'baby',
        'category'   => 'birth',
        'sort_order' => 1,
    ],
    [
        'year'       => 1967,
        'month'      => null,
        'title'      => 'Youth & Studies',
        'description'=> 'Attended the Professor Silva Cunha Technical School in Dili between 1967 and 1969, completing his secondary technical studies. Later continued his studies at the Francisco Xavier Private School, known for his spirit of friendship, solidarity, and dedication.',
        'icon'       => 'school',
        'category'   => 'education',
        'sort_order' => 2,
    ],
    [
        'year'       => 1972,
        'month'      => null,
        'title'      => 'Music & Youth',
        'description'=> 'Between 1972 and the following years, he was a vocalist and played bass in the musical bands Acadêmico, Bealuli, and Lords, demonstrating a passion for music and a vibrant social life from an early age.',
        'icon'       => 'heart',
        'category'   => 'personal',
        'sort_order' => 3,
    ],
    [
        'year'       => 1975,
        'month'      => null,
        'title'      => 'Beginning of Adulthood',
        'description'=> 'Began his professional life in different fields, gaining experience through administrative work, commerce, and business. He stood out for his dedication, responsibility, discipline, and entrepreneurial spirit.',
        'icon'       => 'briefcase',
        'category'   => 'work',
        'sort_order' => 4,
    ],
    [
        'year'       => 1975,
        'month'      => null,
        'title'      => 'Marriage & Family',
        'description'=> 'Met Ana Carrascalão in Dili during the 1970s and later married, building a family with their children: Holdérico Campos, Bebé Campos, Hércio Campos, Nino Campos, and Heglise Campos. As a father, he was demanding in love, caring, and inspiring.',
        'icon'       => 'family',
        'category'   => 'family',
        'sort_order' => 5,
    ],
    [
        'year'       => 1980,
        'month'      => null,
        'title'      => 'Resistance Period Support',
        'description'=> 'During the Indonesian occupation, in Jakarta alongside cousin Júlio Alfaro, he received the trust of Commander-in-Chief Kay Rala Xanana Gusmão to support Timorese people leaving East Timor. He also coordinated with entities in Macau to facilitate protection for compatriots.',
        'icon'       => 'star',
        'category'   => 'achievement',
        'sort_order' => 6,
    ],
    [
        'year'       => 1980,
        'month'      => null,
        'title'      => 'Entrepreneurial Career',
        'description'=> 'Starting in the 1980s, became an entrepreneur, excelling in different areas of business and investment. Built a career marked by entrepreneurial vision, dedication, and a strong presence in the private sector of Timor-Leste.',
        'icon'       => 'briefcase',
        'category'   => 'work',
        'sort_order' => 7,
    ],
    [
        'year'       => 2002,
        'month'      => null,
        'title'      => 'Involvement in Political Life',
        'description'=> 'After the restoration of East Timor\'s independence, actively participated in various political and party activities, maintaining his interest in national development and contributing to the political and democratic life of Timor.',
        'icon'       => 'award',
        'category'   => 'achievement',
        'sort_order' => 8,
    ],
    [
        'year'       => 2010,
        'month'      => null,
        'title'      => 'CCI President',
        'description'=> 'Elected President of the Chamber of Commerce and Industry (CCI) of the Dili District, a position he held between 2010 and 2014, leading initiatives related to economic and business development and the strengthening of the private sector in Timor-Leste.',
        'icon'       => 'award',
        'category'   => 'achievement',
        'sort_order' => 9,
    ],
    [
        'year'       => 2020,
        'month'      => null,
        'title'      => 'Recent Years',
        'description'=> 'Dedicated himself especially to his family, grandchildren, spending time with friends, and personal and business projects. Family gatherings, celebrations, and moments of sharing became even more important during this phase of his life.',
        'icon'       => 'sunset',
        'category'   => 'personal',
        'sort_order' => 10,
    ],
    [
        'year'       => 2026,
        'month'      => 5,
        'title'      => 'Passed Away',
        'description'=> 'Hércio Maria das Neves Campos passed away on May 4, 2026, leaving a profound mark on the lives of his family, friends, and all those who had the privilege of knowing him. His memory lives on through the teachings he left behind and the love he transmitted to everyone around him.',
        'icon'       => 'dove',
        'category'   => 'passing',
        'sort_order' => 11,
    ],
];

$stmt = $db->prepare(
    'INSERT INTO timeline (year, month, title, description, icon, category, sort_order, is_active)
     VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
);

$count = 0;
foreach ($events as $ev) {
    $stmt->execute([
        $ev['year'],
        $ev['month'],
        $ev['title'],
        $ev['description'],
        $ev['icon'],
        $ev['category'],
        $ev['sort_order'],
    ]);
    echo sprintf("✓ %d — %s\n", $ev['year'], $ev['title']);
    $count++;
}

echo "\nDone. {$count} timeline events inserted.\n";
echo "⚠️  Delete this file from the server after running.\n";
