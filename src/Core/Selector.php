<?php

namespace Lar\Tagable\Core;

use Illuminate\Support\Collection;
use Lar\Tagable\Tag;

class Selector
{
    /**
     * Condition collection.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * Matches collection.
     *
     * @var Collection
     */
    protected $matches;

    /**
     * Selector constructor.
     */
    public function __construct()
    {
        $this->matches = new Collection();
    }

    public function getRegExp()
    {
        $regexp = <<<REGEXP
^(?'setter'[\.\#])?(?'element'[a-zA-Z\_\-]*)[ ]*(?'follower'\[[ ]*(?'attribute'[a-zA-Z\-\_]*)[ ]*(?'op'[\=\~\|\^\$\*\%\!\>\<\@\.]{1,2}){0,1}[ ]*[\'\"]?(?'value'.*[^\"\'])?[\'\"]?[ ]*\]$)?$
REGEXP;

        return $regexp;
    }

    /**
     * Selector pars one point.
     *
     * @param string $selector
     * @return array
     * @throws \Exception
     */
    public function pars($select)
    {
        $select = trim($select);
        $select = str_replace("\n", '', $select);
        $conditions = [];
        $regexp = $this->getRegExp();

        if (preg_match("/{$regexp}/", $select, $g)) {
            $parse_data = collect([$select] + $g)->filter(function ($i, $k) {
                return is_numeric($k) ? false : true;
            })->diff(['']);

            if ($parse_data->count() == 2 && $parse_data->has('setter') && $parse_data->has('element') && $parse_data->get('setter') == '.') {
                $conditions[$select] = ['attribute' => 'class', 'op' => '%l%', 'value' => $parse_data->get('element')];
            } elseif ($parse_data->count() == 2 && $parse_data->has('setter') && $parse_data->has('element') && $parse_data->get('setter') == '#') {
                $conditions[$select] = ['attribute' => 'id', 'op' => '=', 'value' => $parse_data->get('element')];
            } else {
                if ($parse_data->has('op') && $parse_data->has('value')) {
                    $op = $parse_data->get('op');
                    $val = trim($parse_data->get('value'));
                    $operator = null;
                    $value = null;

                    switch ($op) {
                        case '~=':
                        case '=~':
                        case '*=':
                        case '=*': // css like
                        case '**': // original
                            $operator = '%l%';
                            break;

                        case '|=':
                        case '=|':
                        case '^=':
                        case '=^': // css like
                        case '>>': // original
                            $operator = 'l%';
                            break;

                        case '$=':
                        case '=$': // css like
                        case '<<': // original
                            $operator = '%l';
                            break;

                        case '!=':
                        case '=':  // original
                        case '@=':  // original regexp
                            $operator = $op; // (!=) , (=) , (@=)
                            break;

                        case '@.':
                        case '@': // original regexp
                            $operator = '@=';
                            $value = "^{$val}$";
                            break;

                        case '@^': // original regexp
                            $operator = '@=';
                            $value = "^{$val}";
                            break;

                        case '@$': // original regexp
                            $operator = '@=';
                            $value = "{$val}$";
                            break;

                        case '.=': // original
                            $operator = 'in';
                            $value = array_map('trim', explode(',', $val));
                            break;

                        case '!.': // original
                            $operator = 'not_in';
                            $value = array_map('trim', explode(',', $val));
                            break;

                        default:
                            $operator = '=';
                            break;
                    }

                    if ($value) {
                        $parse_data->put('value', $value);
                    }
                    $parse_data->put('op', $operator);
                }

                $conditions[$select] = $parse_data->toArray();
            }
        }

        return $conditions;
    }

    /**
     * Selector parser.
     *
     * @param string $selector
     * @return Selector
     * @throws \Exception
     */
    public function parser(string $selector)
    {
        $selector = explode(';', $selector);

        foreach ($selector as $select) {
            $this->conditions = array_merge($this->conditions, $this->pars($select));
        }

        return $this;
    }

    /**
     * Put data in matches.
     *
     * @param string $selector
     * @param Tag $tag
     * @return Selector
     */
    public function matchesPut(string $selector, Tag $tag)
    {
        if (! $this->matches->has($selector)) {
            $this->matches->put($selector, new MatchesCollection());
        }

        $this->matches->get($selector)->put($tag['id'], $tag);

        return $this;
    }

    /**
     * Compare condition and any instance Tag class.
     *
     * @param array $condition
     * @param Tag $tag
     * @return bool
     */
    public function compare(array $condition, $tag)
    {
        if (isset($condition['attribute']) && isset($condition['op']) && isset($condition['value']) && $tag->hasAttribute($condition['attribute'])) {
            if ($condition['op'] == '=') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_string($attr) && $condition['value'] == $attr) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == '!=') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_string($attr) && $condition['value'] != $attr) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == '%l%') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_string($attr) && preg_match("/({$condition['value']})/", $attr)) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == '%l') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_string($attr) && preg_match("/({$condition['value']})$/", $attr)) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == 'l%') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_string($attr) && preg_match("/^({$condition['value']})/", $attr)) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == 'in') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_array($condition['value']) && array_search($attr, $condition['value']) !== false) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == 'not_in') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_array($condition['value']) && array_search($attr, $condition['value']) === false) {
                        return true;
                    }
                }
            } elseif ($condition['op'] == '@=') {
                if ($attr = $tag->getAttribute($condition['attribute'])) {
                    if (is_string($attr) && preg_match("/{$condition['value']}/", $attr)) {
                        return true;
                    }
                }
            }
        } elseif ((isset($condition['attribute']) && count($condition) == 2) || (isset($condition['attribute']) && isset($condition['element']) && count($condition) == 3)) {
            if ($tag->hasAttribute($condition['attribute'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find tags in to collection by conditions.
     *
     * @return array|Collection
     */
    public function find()
    {
        $collect = Tag::$collect;

        foreach ($this->conditions as $selector => $condition) {
            $filtered_collect = null;

            if (isset($condition['element'])) {
                $filtered_collect = $collect->where('element', $condition['element']);

                if (count($condition) == 1) {
                    $filtered_collect->map(function (Tag $tag) use ($selector) {
                        $this->matchesPut($selector, $tag);
                    });

                    continue;
                }
            } else {
                $filtered_collect = $collect;
            }

            $filtered_collect->map(function (Tag $tag) use ($selector, $condition) {
                if ($this->compare($condition, $tag)) {
                    $this->matchesPut($selector, $tag);
                }

                return $tag;
            });
        }

        return $this->matches;
    }
}
