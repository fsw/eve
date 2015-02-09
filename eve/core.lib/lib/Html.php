<?php

/** 
 * @package Core
 * @author fsw
 */
class Html
{

    private static function attrs($attrs)
    {
        $ret = '';
        foreach ($attrs as $key => $value) {
            $ret .= ' ' . $key . '="' . $value . '"';
        }
        return $ret;
    }

    public static function select($attrs, $options, $value = null)
    {
        $ret[] = '<select' . self::attrs($attrs) . '>';
        foreach ($options as $key => $val) {
            $ret[] = '<option value="' . $key . '"' . ($value == $key ? ' selected="selected"' : '') . '>' . $val .
                     '</option>';
        }
        $ret[] = '</select>';
        return implode('', $ret);
    }

    public static function menu($attrs, $data)
    {
        $ret[] = '<ul' . self::attrs($attrs) . '>';
        foreach ($data as $key => &$value) {
            if (is_string($value)) {
                $value = array('href' => $value, 'title' => $key);
            }
        }
        foreach ($data as $row) {
            $a = array();
            if (! empty($row['current'])) {
                $a['class'] = 'current';
            }
            $ret[] = '<li' . self::attrs($a) . '>';
            $ret[] = '<a href="' . $row['href'] . '">' . $row['title'] . '</a>';
            if (! empty($row['children'])) {
                $ret[] = self::menu(array(), $row['children']);
            }
            $ret[] = '</li>';
        }
        $ret[] = '</ul>';
        return implode('', $ret);
    }
    
    // TOREMOVE
    public static function ulTree($data, $callback, $subKey = 'children', $ulAttrs = array(), $liAttrs = array())
    {
        $ret[] = '<ul' . self::attrs($ulAttrs) . '>';
        foreach ($data as $row) {
            $attrs = $liAttrs;
            if (! empty($row['class'])) {
                $attrs['class'] = $row['class'];
            }
            $ret[] = '<li' . self::attrs($attrs) . '>';
            $ret[] = $callback($row);
            if (! empty($row[$subKey])) {
                $ret[] = self::ulTree($row[$subKey], $callback, $subKey);
            }
            $ret[] = '</li>';
        }
        $ret[] = '</ul>';
        return implode('', $ret);
    }
}