<?php

namespace MediaWiki\Extension\CMFStore;

use Parser, OutputPage, Skin;

/**
 * Class MW_EXT_List
 */
class MW_EXT_List
{
  /**
   * Get JSON data.
   *
   * @param $type
   *
   * @return array
   */
  private static function getList($type)
  {
    $get = MW_EXT_Kernel::getJSON(__DIR__ . '/storage/list.json');
    $out = $get['list'][$type] ?? [] ?: [];

    return $out;
  }

  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return bool
   * @throws \MWException
   */
  public static function onParserFirstCallInit(Parser $parser)
  {
    $parser->setFunctionHook('list', [__CLASS__, 'onRenderTag']);

    return true;
  }

  /**
   * Render tag function.
   *
   * @param Parser $parser
   * @param string $type
   * @param string $style
   *
   * @return null|string
   */
  public static function onRenderTag(Parser $parser, $type = '', $style = '')
  {
    // Argument: type.
    $getType = MW_EXT_Kernel::outClear($type ?? '' ?: '');
    $outType = MW_EXT_Kernel::outNormalize($getType);

    // Check license type, set error category.
    if (!self::getList($outType)) {
      $parser->addTrackingCategory('mw-ext-list-error-category');

      return null;
    }

    // Argument: style.
    $getStyle = MW_EXT_Kernel::outClear($style ?? '' ?: '');
    $outStyle = MW_EXT_Kernel::outNormalize($getStyle);

    // Get data.
    $getList = self::getList($outType);

    // Sort data.
    asort($getList);

    // Build style class.
    switch ($outStyle) {
      case 'grid':
        $outClass = 'grid';
        break;
      case 'list':
        $outClass = 'list';
        break;
      case 'inline':
        $outClass = 'inline';
        break;
      default:
        $parser->addTrackingCategory('mw-ext-list-error-category');

        return null;
    }

    // Set URL item.
    $outItem = '';

    foreach ($getList as $item) {
      $title = empty($item['title']) ? '' : $item['title'];
      $icon = empty($item['icon']) ? 'fas fa-globe' : $item['icon'];
      $bg_color = empty($item['style']['background-color']) ? '' : 'background-color:' . $item['style']['background-color'] . ';';
      $color = empty($item['style']['color']) ? '' : 'color:' . $item['style']['color'] . ';';
      $href = empty($item['url']) ? '' : $item['url'];
      $desc = MW_EXT_Kernel::getMessageText('list', $item['description']);

      if ($outClass === 'grid') {
        $outItem .= '<div>';
        $outItem .= '<div><a style="' . $bg_color . $color . '" title="' . $desc . '" href="' . $href . '" target="_blank"><i class="' . $icon . '"></i></a></div>';
        $outItem .= '<div><h4><a href="' . $href . '" target="_blank">' . $title . '</a></h4><div>' . $desc . '</div></div>';
        $outItem .= '</div>';
      } else if ($outClass === 'list') {
        $outItem .= '<li><a title="' . $desc . '" href="' . $href . '" target="_blank">' . $title . '</a></li>';
      }
    }

    // Out HTML.
    $outHTML = '<div class="mw-ext-list mw-ext-list-' . $outClass . '">';

    if ($outClass === 'grid') {
      $outHTML .= '<div class="mw-ext-list-body"><div class="mw-ext-list-content">' . $outItem . '</div></div>';

    } else if ($outClass === 'list') {
      $outHTML .= '<div class="mw-ext-list-body"><ul class="mw-ext-list-content">' . $outItem . '</ul></div>';
    }

    $outHTML .= '</div>';

    // Out parser.
    $outParser = $parser->insertStripItem($outHTML, $parser->mStripState);

    return $outParser;
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return bool
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin)
  {
    $out->addModuleStyles(['ext.mw.list.styles']);

    return true;
  }
}
