<?php 

namespace App\Service;

/**
 * Class ArticleParser
 * @package App\Service
 *
 * This class parses articles from a JSON file loaded with the JsonLoader service
 */
class ArticleParser
{
    private array $all_articles = [];
    private array $articles = [];
    private array $authors = [];
    private array $categories = [];
    private array $data;
    private JsonLoader $jsonloader;
    private array $blocks;
    
    private const FIELD_IMAGE_MAIN = 'field_image_main';
    private const FIELD_IMAGE_BACKGROUND = 'field_image_background';
    private const URI = 'uri';
    private const FIELD_SECTION_RB = 'field_section_rb';
    private const NAME = 'name';
    private const TYPE = 'type';
    private const FIELD_BYLINE = 'field_byline';

    private const SKIP_TYPES = [
        "media",
        "magazine_issue",
        "teaser"
    ];

    private const BLOCK_NAMES = [
        "views_block__global_recirculation_most_recent",
        "views_block__global_recirculation_rb_essential_resources",
        "views_block__global_recirculation_recent_magazine",
        "views_block__media_kit_rb",
        "views_block__rb_front_page_expert_insights",
        "views_block__rb_front_page_rb_podcast",
        "views_block__rb_front_page_tdn2k",
        "views_block__rb_infinite_scroll_post",
        "views_block__rb_section_landings_more_on_topic",
        "views_block__rb_section_landings_multimedia",
        "views_block__rb_section_landings_rb_podcasts",
        "views_block__sponsor_content_most_recent",
        "views_block__taxonomy_term_recirc",
        "views_block__winsight_membership_most_recent_premium",
        "views_block__winsight_membership_most_recent_premium_node"
    ];

    /**
     * ArticleParser constructor.
     *
     * @param JsonLoader $jsonloader The JsonLoader service that loads the JSON source file
     *
     * Get the data and separate the blocks
     */
    public function __construct(JsonLoader $jsonloader)
    {
        $this->jsonloader = $jsonloader;
        $this->data = $this->jsonloader->getData();
        $this->blocks = $this->data['content']['blocks'];
    }

    /**
     * @param string $name The name of the block to parse. The private variable BLOCK_NAMES contains all available names to put here
     *
     * This function parses a specific block from the data
     *
     * @return array The parsed block data
     */
    private function parseBlock(string $name): array 
    {
        return isset($this->blocks[$name]["data"]) ? [
            'data'    => $this->blocks[$name]["data"],
            'records' => $this->blocks[$name]["data"]["records"],
            'count'   => count($this->blocks[$name]["data"]["records"]),
            'title'   => $this->blocks[$name]["data"]["title"]
        ] : [];
    }

    /**
     * @param array $data the specific record to parse
     *
     * This function parses the records from the data and separates the data
     * into more usable parts
     */
    private function parseRecords(array $data): void {
        $dataCount = count($data);
        for ($i = 0; $i < $dataCount; $i++) {
            $post = $data[$i];
            if (!in_array($post[self::TYPE], self::SKIP_TYPES)) {
                $this->groupPostBySection($post);
                $this->addPostToAllArticles($post);
            }
        }
    }
    
    /**
     * @param array $post The post to group by section (category)
     *
     * This function groups a given post by its section. It will also 
     * add authors to the author list as it goes through if there is a new 
     * author
     */
    private function groupPostBySection(array $post) {
        if (array_key_exists("field_section_rb", $post)) {
            $section = strtolower($post["field_section_rb"]["name"]);
            $uri = $post["field_section_rb"]["uri"];
        
            if (!array_key_exists($section, $this->articles)) {
                $this->articles[$section]['articles'] = array();
                $this->articles[$section]['previews'] = array();
            }
        
            $posts = array_column($this->articles[$section]['articles'], 'uri');
        
            if (!in_array($post['uri'], $posts) and $post["type"] === "post") {
                array_push($this->articles[$section]['previews'], $this->parsePreview($post));
                array_push($this->articles[$section]['articles'], $post);
            }

            $names = array_column($this->categories, 'name');

            if (!in_array($section, $names)) {
                array_push($this->categories, [
                    "name" => $section,
                    "uri"  => $uri
                ]);
            }
        }
        if ($post["type"] === "profile") {
            $profile = $this->extractProfile($post);
            if (!array_key_exists($profile["uri"], $this->authors) && $profile['image'] !== null) {
                $this->authors[$profile["uri"]] = $profile;
            }
        }

    }

    /**
     * @param array $profile The profile to extract.
     *
     * Extracts the data needed from the $profile data
     *
     * @return array The data for the profile
     */
    private function extractProfile(array $profile): array {
        // Extract and add unique authors
        return [
            "uri" => $profile["uri"],
            "title" => $profile["title"],
            "summary" => $profile["body"]["summary"],
            "value" => $profile["body"]["value"],
            "email" => $profile["field_email_address"],
            "job"   => $profile["field_job_title"],
            "image" => $profile["field_image_main"]["field_image_background"]["uri"]
        ];
    }
    
    /**
     * @param array $post adds posts to the all_articles private variable
     *
     * adds a given post to the list of all articles
     */
    private function addPostToAllArticles(array $post) {
        $all_posts = array_column($this->all_articles, 'uri');
    
        if (!in_array($post['uri'], $all_posts) and $post["type"] === "post") {
            array_push($this->all_articles, $this->parsePreview($post));
        }
    }

    /**
     * @param array $data The data to parse into a preview.
     *
     * parses necessary data for a preview
     *
     * @return array preview data
     */
    private function parsePreview(array $data): array {
        $uri = null;
        $authors = null;
        
        $authors = $this->extractByline($data);
    
        if (!empty($data[self::FIELD_IMAGE_MAIN][self::FIELD_IMAGE_BACKGROUND][self::URI])) {
            $uri = $data[self::FIELD_IMAGE_MAIN][self::FIELD_IMAGE_BACKGROUND][self::URI];
        }
    
        return [
            'title'       => $data['title'],
            'uri'         => "/articles".$data[self::URI],
            'summary'     => $data['body']['summary'],
            'image'       => $uri,
            'authors'     => $authors,
            'category'    => $data[self::FIELD_SECTION_RB][self::NAME],
            'category_uri'=> $data[self::FIELD_SECTION_RB][self::URI],
            'date'        => $data['field_published_date']
        ];
    }

    /**
     * @param array $data The data to extract the byline
     *
     * Parses author data for each author that should be credited
     * in the Byline
     *
     * @return array byline data.
     */
    private function extractByline(array $data): array {
        return (count($data[self::FIELD_BYLINE]) > 0) ? array_map(fn($author) => [
                    'title' => $author['title'] ?? '',
                    'uri' => $author['uri'] ?? ''
                ], $data[self::FIELD_BYLINE])
                : [];
    }

    /**
     * parses the data from a JSON file and returns the parsed articles, categories, and authors.
     *
     * @return array The parsed data
     */
    public function parse(): array
    {
        foreach (self::BLOCK_NAMES as $blockName) {
            $parsedBlock = $this->parseBlock($blockName);
            $this->parseRecords($parsedBlock['records']);
        }

        return [
            'articles'     => $this->articles,
            'all_articles' => $this->all_articles,
            'categories'   => $this->categories,
            'authors'      => $this->authors
        ];
    }
}