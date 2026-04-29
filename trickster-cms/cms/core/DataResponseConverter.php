<?php

abstract class DataResponseConverter
{
    /**
     * @param JsonDataProvider[] $data
     * @return array
     */
    abstract function convert($data);

    protected function htmlToPlainText(?string $src): string
    {
        $result = $src ?? '';
        $result = html_entity_decode($result, ENT_QUOTES);
        $result = preg_replace('/[\x0A]*/', '', $result);
        $result = preg_replace('#[\n\r\t]#', "", $result);
        $result = preg_replace('#[\s]+#', " ", $result);
        $result = preg_replace('#(</li>|</div>|</td>|</tr>|<br />|<br/>|<br>)#ui', "$1\n", $result);
        $result = preg_replace('#(</h1>|</h2>|</h3>|</h4>|</h5>|</p>)#ui', "$1\n\n", $result);
        $result = strip_tags($result);
        $result = preg_replace('#^ +#m', "", $result); //left trim whitespaces on each line
        $result = preg_replace('#([\n]){2,}#', "\n\n", $result); //limit newlines to 2 max
        return trim($result);
    }
}