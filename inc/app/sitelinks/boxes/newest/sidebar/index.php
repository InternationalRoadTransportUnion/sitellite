<?php

loader_import ('sitelinks.Item');
loader_import ('sitelinks.Filters');

$i = new SiteLinks_Item;

$parameters['list'] = $i->getNewest (appconf ('limit'));

echo template_simple ('sidebar.spt', $parameters);

?>