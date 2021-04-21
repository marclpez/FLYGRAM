<?php
namespace MMP;

use MMP\Maps_Marker_Pro as MMP;

class Layers {
	/**
	 * Retrieves the basemaps
	 *
	 * @since 4.14
	 *
	 * @param bool $all (optional) Whether to return all or only available basemaps
	 * @param bool $custom (optional) Whether to include custom basemaps
	 */
	public function get_basemaps($all = false, $custom = true) {
		$db = MMP::get_instance('MMP\DB');

		$osm = esc_html__('Map', 'mmp') . ': &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap ' . esc_html__('contributors', 'mmp') . '</a>';

		$osm_france = esc_html__('Map', 'mmp') . ': &copy; <a href="https://www.openstreetmap.fr" target="_blank">OpenStreetMap France</a> &amp; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap ' . esc_html__('contributors', 'mmp') . '</a>';

		$osm_hot = esc_html__('Map', 'mmp') . ': &copy; <a href="https://www.openstreetmap.fr" target="_blank">OpenStreetMap France</a> &amp; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap ' . esc_html__('contributors', 'mmp') . '</a>, ' . esc_html__('Tiles courtesy of', 'mmp') . ' <a href="https://hotosm.org" target="_blank">Humanitarian OpenStreetMap Team</a>';

		$stamen = esc_html__('Map tiles by', 'mmp') . ' <a href="http://stamen.com" target="_blank">Stamen Design</a>, ' . esc_html__('under', 'mmp') . ' <a href="http://creativecommons.org/licenses/by/3.0" target="_blank">CC BY 3.0</a>. ' . esc_html__('Data by', 'mmp') . ' <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a>, ' . esc_html__('under', 'mmp') . ' <a href="http://www.openstreetmap.org/copyright" target="_blank">ODbL</a>.';

		$stamen_watercolor = esc_html__('Map tiles by', 'mmp') . ' <a href="http://stamen.com" target="_blank">Stamen Design</a>, ' . esc_html__('under', 'mmp') . ' <a href="http://creativecommons.org/licenses/by/3.0" target="_blank">CC BY 3.0</a>. ' . esc_html__('Data by', 'mmp') . ' <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a>, ' . esc_html__('under', 'mmp') . ' <a href="http://creativecommons.org/licenses/by-sa/3.0" target="_blank">CC BY SA</a>.';

		$basemap_at = esc_html__('Map', 'mmp') . ': &copy; <a href="https://www.basemap.at" target="_blank">basemap.at</a>';

		$basemaps['osm'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'OpenStreetMap',
			'url'     => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abc',
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19,
				'attribution'   => $osm
			)
		);
		$basemaps['osmBw'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'OpenStreetMap (Black and White)',
			'url'     => 'https://tiles.wmflabs.org/bw-mapnik/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abc',
				'minNativeZoom' => 1,
				'maxNativeZoom' => 18,
				'attribution'   => $osm
			)
		);
		$basemaps['osmDe'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'OpenStreetMap (DE)',
			'url'     => 'https://tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abc',
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19,
				'attribution'   => $osm
			)
		);
		$basemaps['osmFrance'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'OpenStreetMap (France)',
			'url'     => 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abc',
				'minNativeZoom' => 1,
				'maxNativeZoom' => 20,
				'attribution'   => $osm_france
			)
		);
		$basemaps['osmHot'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'OpenStreetMap (HOT)',
			'url'     => 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abc',
				'minNativeZoom' => 1,
				'maxNativeZoom' => 20,
				'attribution'   => $osm_hot
			)
		);
		$basemaps['stamenTerrain'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Terrain)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 18,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenTerrainBackground'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Terrain Background)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/terrain-background/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 18,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenTerrainLines'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Terrain Lines)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/terrain-lines/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 18,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenToner'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Toner)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 20,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenTonerBackground'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Toner Background)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner-background/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 20,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenTonerHybrid'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Toner Hybrid)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner-hybrid/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 17,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenTonerLines'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Toner Lines)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lines/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 18,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenTonerLite'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Toner Lite)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 0,
				'maxNativeZoom' => 20,
				'attribution'   => $stamen
			)
		);
		$basemaps['stamenWatercolor'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'Stamen (Watercolor)',
			'url'     => 'https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.png',
			'options' => array(
				'subdomains'    => 'abcd',
				'minNativeZoom' => 1,
				'maxNativeZoom' => 18,
				'attribution'   => $stamen_watercolor
			)
		);
		$basemaps['basemapAt'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'basemap.at',
			'url'     => 'https://{s}.wien.gv.at/basemap/geolandbasemap/normal/google3857/{z}/{y}/{x}.png',
			'options' => array(
				'bounds'        => array([46.358770, 8.782379], [49.037872, 17.5]),
				'subdomains'    => array('maps', 'maps1', 'maps2', 'maps3', 'maps4'),
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19,
				'attribution'   => $basemap_at
			)
		);
		$basemaps['basemapAtSatellite'] = array(
			'type'    => 1,
			'wms'     => 0,
			'name'    => 'basemap.at (Satellite)',
			'url'     => 'https://{s}.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/{z}/{y}/{x}.jpeg',
			'options' => array(
				'bounds'        => array([46.358770, 8.782379], [49.037872, 17.5]),
				'subdomains'    => array('maps', 'maps1', 'maps2', 'maps3', 'maps4'),
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19,
				'attribution'   => $basemap_at
			)
		);

		if ($all || MMP::$settings['googleApiKey']) {
			$basemaps['googleRoadmap'] = array(
				'type'    => 2,
				'name'    => 'Google (Roadmap)',
				'options' => array(
					'type'   => 'roadmap'
				)
			);
			$basemaps['googleSatellite'] = array(
				'type'    => 2,
				'name'    => 'Google (Satellite)',
				'options' => array(
					'type'   => 'satellite'
				)
			);
			$basemaps['googleHybrid'] = array(
				'type'    => 2,
				'name'    => 'Google (Hybrid)',
				'options' => array(
					'type'   => 'hybrid'
				)
			);
			$basemaps['googleTerrain'] = array(
				'type'    => 2,
				'name'    => 'Google (Terrain)',
				'options' => array(
					'type'   => 'terrain'
				)
			);
		}

		if ($all || MMP::$settings['bingApiKey']) {
			$basemaps['bingRoad'] = array(
				'type'    => 3,
				'name'    => 'Bing (Road)',
				'options' => array(
					'imagerySet'    => 'Road',
					'minNativeZoom' => 1,
					'maxNativeZoom' => 19
				)
			);
			$basemaps['bingAerial'] = array(
				'type'    => 3,
				'name'    => 'Bing (Aerial)',
				'options' => array(
					'imagerySet'    => 'Aerial',
					'minNativeZoom' => 1,
					'maxNativeZoom' => 19
				)
			);
			$basemaps['bingAerialLabels'] = array(
				'type'    => 3,
				'name'    => 'Bing (Aerial with Labels)',
				'options' => array(
					'imagerySet'    => 'AerialWithLabels',
					'minNativeZoom' => 1,
					'maxNativeZoom' => 19
				)
			);
			$basemaps['bingCanvasDark'] = array(
				'type'    => 3,
				'name'    => 'Bing (Canvas Dark)',
				'options' => array(
					'imagerySet'    => 'CanvasDark',
					'minNativeZoom' => 1,
					'maxNativeZoom' => 19
				)
			);
			$basemaps['bingCanvasLight'] = array(
				'type'    => 3,
				'name'    => 'Bing (Canvas Light)',
				'options' => array(
					'imagerySet'    => 'CanvasLight',
					'minNativeZoom' => 1,
					'maxNativeZoom' => 19
				)
			);
			$basemaps['bingCanvasGray'] = array(
				'type'    => 3,
				'name'    => 'Bing (Canvas Gray)',
				'options' => array(
					'imagerySet'    => 'CanvasGray',
					'minNativeZoom' => 1,
					'maxNativeZoom' => 19
				)
			);
		}

		if ($all || MMP::$settings['hereApiKey'] || (MMP::$settings['hereAppId'] && MMP::$settings['hereAppCode'])) {
			$basemaps['hereNormalDay'] = array(
				'type'    => 4,
				'name'    => 'HERE (Normal Day)',
				'options' => array(
					'scheme'        => 'normal.day',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 20
				)
			);
			$basemaps['hereNormalNight'] = array(
				'type'    => 4,
				'name'    => 'HERE (Normal Night)',
				'options' => array(
					'scheme'        => 'normal.night',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 20
				)
			);
			$basemaps['hereTerrain'] = array(
				'type'    => 4,
				'name'    => 'HERE (Terrain)',
				'options' => array(
					'scheme'        => 'terrain.day',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 20
				)
			);
			$basemaps['hereSatellite'] = array(
				'type'    => 4,
				'name'    => 'HERE (Satellite)',
				'options' => array(
					'scheme'        => 'satellite.day',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 20
				)
			);
			$basemaps['hereHybrid'] = array(
				'type'    => 4,
				'name'    => 'HERE (Hybrid)',
				'options' => array(
					'scheme'        => 'hybrid.day',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 20
				)
			);
		}

		if ($all || MMP::$settings['tomApiKey']) {
			$basemaps['tom'] = array(
				'type'    => 5,
				'name'    => 'TomTom',
				'options' => array(
					'style'         => 'main',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 22
				)
			);
			$basemaps['tomNight'] = array(
				'type'    => 5,
				'name'    => 'TomTom (Night)',
				'options' => array(
					'style'         => 'night',
					'minNativeZoom' => 0,
					'maxNativeZoom' => 22
				)
			);
		}

		$basemaps['esriStreets'] = array(
			'type'    => 6,
			'name'    => 'ESRI Streets',
			'key'     => 'Streets',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19
			)
		);
		$basemaps['esriTopographic'] = array(
			'type'    => 6,
			'name'    => 'ESRI Topographic',
			'key'     => 'Topographic',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19
			)
		);
		$basemaps['esriNationalGeographic'] = array(
			'type'    => 6,
			'name'    => 'ESRI National Geographic',
			'key'     => 'NationalGeographic',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 16
			)
		);
		$basemaps['esriGray'] = array(
			'type'    => 6,
			'name'    => 'ESRI Gray',
			'key'     => 'Gray',
			'labels'  => 'GrayLabels',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 16
			)
		);
		$basemaps['esriDarkGray'] = array(
			'type'    => 6,
			'name'    => 'ESRI Dark Gray',
			'key'     => 'DarkGray',
			'labels'  => 'GrayLabels',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 16
			)
		);
		$basemaps['esriOceans'] = array(
			'type'    => 6,
			'name'    => 'ESRI Oceans',
			'key'     => 'Oceans',
			'labels'  => 'OceansLabels',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 16
			)
		);
		$basemaps['esriImagery'] = array(
			'type'    => 6,
			'name'    => 'ESRI Imagery',
			'key'     => 'Imagery',
			'labels'  => 'ImageryLabels',
			'options' => array(
				'minNativeZoom' => 1,
				'maxNativeZoom' => 19
			)
		);

		if (!$all) {
			foreach ($basemaps as $key => $basemap) {
				if (in_array($key, MMP::$settings['disabledBasemaps'])) {
					unset($basemaps[$key]);
				}
			}
		}

		if ($custom) {
			foreach ($db->get_all_basemaps() as $custom) {
				$basemaps[$custom->id] = array(
					'type'    => 1,
					'wms'     => absint($custom->wms),
					'name'    => $custom->name,
					'url'     => $custom->url,
					'options' => json_decode($custom->options)
				);
			}
		}

		return $basemaps;
	}

	/**
	 * Retrieves the overlays
	 *
	 * @since 4.14
	 */
	public function get_overlays() {
		$db = MMP::get_instance('MMP\DB');

		$overlays = array();
		foreach ($db->get_all_overlays() as $custom) {
			$overlays[$custom->id] = array(
				'wms'     => absint($custom->wms),
				'name'    => $custom->name,
				'url'     => $custom->url,
				'options' => json_decode($custom->options)
			);
		}

		return $overlays;
	}
}
