/*
* Automated Testing Kit configuration.
*/
var currentEnv = process.env.TERMINUS_ENV || 'local';
var runOnPantheon = false;
if (currentEnv != 'local') {
  runOnPantheon = true;
}

module.exports = {
  operatingMode: "native",
  drushCmd: "__DRUSH_CMD__",
  articleAddUrl: 'node/add/article',
  contactUsUrl: "form/contact",
  logInUrl: "user/login",
  logOutUrl: "user/logout",
  imageAddUrl: 'media/add/image',
  mediaDeleteUrl: 'media/{mid}/delete',
  mediaEditUrl: 'media/{mid}/edit',
  mediaList: 'admin/content/media',
  menuAddUrl: 'admin/structure/menu/manage/main/add',
  menuDeleteUrl: 'admin/structure/menu/item/{mid}/delete',
  menuEditUrl: 'admin/structure/menu/item/{mid}/edit',
  menuListUrl: 'admin/structure/menu/manage/main',
  nodeDeleteUrl: 'node/{nid}/delete',
  nodeEditUrl: 'node/{nid}/edit',
  pageAddUrl: 'node/add/page',
  registerUrl: "user/register",
  resetPasswordUrl: "user/password",
  termAddUrl: 'admin/structure/taxonomy/manage/terms/add',
  termEditUrl: 'taxonomy/term/{tid}/edit',
  termDeleteUrl: 'taxonomy/term/{tid}/delete',
  termListUrl: 'admin/structure/taxonomy/manage/terms/overview',
  termViewUrl: 'taxonomy/term/{tid}',
  xmlSitemapUrl: 'admin/config/search/simplesitemap',
  authDir: "tests/support",
  dataDir: "tests/data",
  supportDir: "tests/support",
  testDir: "tests",
  pantheon : {
    isTarget: runOnPantheon,
    site: "__PANTHEON_SITE__",
    environment: currentEnv
  }
}
