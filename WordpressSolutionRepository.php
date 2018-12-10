<?php
namespace Models\Solutions;
use  WPAPI\WPAPI;
use App;

class WordpressSolutionRepository implements SolutionRepository {

    public function fetchSolution($slug, $intl)
    {
        $query = [
            'slug' => $slug
        ];

        if(!empty($intl)) {
            $query['post_type'] = 'intl_landing_page';
        }

        $params = array('endpoint' => 'get_landing_page');

        $wordpress = new WPAPI($params);
        $solution = $wordpress->requestData($query);

        if(count($solution->content) === 0) {
            App::abort(404, "No results for solution " . $slug);
        }

        return $solution;
    }
}