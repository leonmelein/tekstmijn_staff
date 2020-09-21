<?php

namespace BootPress\Table;

use BootPress\Page\Component as Page;

class Component
{
    private $page;
    private $head = false; // or true
    private $foot = false; // or true
    private $body = false; // or true
    private $cell = ''; // or a closing </th> or </td> tag
    private $vars = array(); // to include in every cell

    public function __construct()
    {
        $this->page = Page::html();
    }

    /**
     * Create a ``<table>``.
     * 
     * @param string|array $vars    ``<table>`` attributes.
     * @param string       $caption Table ``<caption>``.
     * 
     * @return string
     */
    public function open($vars = '', $caption = '')
    {
        if (!empty($caption)) {
            $caption = '<caption>'.$caption.'</caption>';
        }

        return "\n".$this->page->tag('table', $this->values($vars)).$caption;
    }

    /**
     * Create a ``<thead>`` row.
     * 
     * @param string|array $vars  ``<tr>`` attributes.
     * @param string|array $cells ``<th>`` attributes for all of this rows cells.
     * 
     * @return string
     */
    public function head($vars = '', $cells = '')
    {
        $html = $this->wrapUp('head')."\n\t";
        $this->head = true;
        $this->vars = (!empty($cells)) ? $this->values($cells) : array();

        return $html.$this->page->tag('tr', $this->values($vars));
    }

    /**
     * Create a ``<tfoot>`` row.
     * 
     * @param string|array $vars  ``<tr>`` attributes.
     * @param string|array $cells ``<td>`` attributes for all of this rows cells.
     * 
     * @return string
     */
    public function foot($vars = '', $cells = '')
    {
        $html = $this->wrapUp('foot')."\n\t";
        $this->foot = true;
        $this->vars = (!empty($cells)) ? $this->values($cells) : array();

        return $html.$this->page->tag('tr', $this->values($vars));
    }

    /**
     * Create a ``<tbody>`` row.
     * 
     * @param string|array $vars  ``<tr>`` attributes.
     * @param string|array $cells ``<td>`` attributes for all of this rows cells.
     * 
     * @return string
     */
    public function row($vars = '', $cells = '')
    {
        $html = $this->wrapUp('row')."\n\t";
        $this->body = true;
        $this->vars = (!empty($cells)) ? $this->values($cells) : array();

        return $html.$this->page->tag('tr', $this->values($vars));
    }

    /**
     * Create a ``<th>`` or ``<td>`` cell.
     * 
     * @param string|array $vars    The cell's attributes.
     * @param string       $content The (optional) cell's value.
     * 
     * @return string
     */
    public function cell($vars = '', $content = '')
    {
        $html = $this->wrapUp('cell');
        $tag = ($this->head) ? 'th' : 'td';
        $this->cell = '</'.$tag.'>';
        $vars = $this->values($vars);
        if (!empty($this->vars)) {
            $vars = array_merge($this->vars, $vars);
        }

        return $html.$this->page->tag($tag, $vars).$content;
    }

    /**
     * Closes any remaining open tags.
     * 
     * @return string
     */
    public function close()
    {
        $html = $this->wrapUp('table')."\n";
        $this->head = false;
        $this->foot = false;
        $this->body = false;
        $this->cell = '';
        $this->vars = '';

        return $html.'</table>';
    }

    /**
     * Converts a '**|**' (single pipe) separated string to an array of attributes.
     * 
     * @param string|array $vars Attributes
     * 
     * @return array
     */
    protected function values($vars)
    {
        if (is_array($vars)) {
            return $vars;
        }
        $attributes = array();
        foreach (explode('|', $vars) as $value) {
            if (strpos($value, '=')) {
                list($key, $value) = explode('=', $value);
                $attributes[trim($key)] = trim($value);
            }
        }

        return $attributes;
    }

    /**
     * Closes a row's open tags to be ready for the next one.
     * 
     * @param <type> $section
     * 
     * @return <type>
     */
    private function wrapUp($section)
    {
        $html = $this->cell;
        $this->cell = '';
        switch ($section) {
            case 'head':
                if ($this->head) {
                    $html .= '</tr>';
                } else {
                    if ($this->foot) {
                        $html .= '</tr></tfoot>';
                        $this->foot = false;
                    } elseif ($this->body) {
                        $html .= '</tr></tbody>';
                        $this->body = false;
                    }
                    $html .= '<thead>';
                }
                break;
            case 'foot':
                if ($this->foot) {
                    $html .= '</tr>';
                } else {
                    if ($this->head) {
                        $html .= '</tr></thead>';
                        $this->head = false;
                    } elseif ($this->body) {
                        $html .= '</tr></tbody>';
                        $this->body = false;
                    }
                    $html .= '<tfoot>';
                }
                break;
            case 'row':
                if ($this->body) {
                    $html .= '</tr>';
                } else {
                    if ($this->head) {
                        $html .= '</tr></thead>';
                        $this->head = false;
                    } elseif ($this->foot) {
                        $html .= '</tr></tfoot>';
                        $this->foot = false;
                    }
                    $html .= '<tbody>';
                }
                break;
            case 'table':
                if ($this->head) {
                    $html .= '</tr></thead>';
                } elseif ($this->foot) {
                    $html .= '</tr></tfoot>';
                } elseif ($this->body) {
                    $html .= '</tr></tbody>';
                }
                break;
        }

        return $html;
    }
}
