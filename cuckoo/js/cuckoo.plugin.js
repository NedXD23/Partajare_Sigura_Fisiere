(function() {

  OCA.cuckoo = OCA.cuckoo || {};

  /**
   * @namespace
   */
  OCA.cuckoo.Util = {

    /**
     * Initialize the cuckoo plugin.
     *
     * @param {OCA.Files.FileList} fileList file list to be extended
     */
    attach: function(fileList) {

      if (fileList.id === 'trashbin' || fileList.id === 'files.public') {
        return;
      }

      fileList.registerTabView(new OCA.cuckoo.cuckooTabView('cuckooTabView', {}));

    }
  };
})();

OC.Plugins.register('OCA.Files.FileList', OCA.cuckoo.Util);
