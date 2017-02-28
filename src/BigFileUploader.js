;function BigFileUploader(file, options) {
    this.file = file;
    this.options = $.extend({
        blockSize: 10,
        url: '/upload',
        error: null,
        success: null,
        progress: null
    }, options);

    if (typeof this.init !== 'function') {
        BigFileUploader.prototype.init = function () {
            if (typeof this.file.files[0] === 'undefined') {
                this.handleError({
                    message: '请选择要上传的文件'
                });

                return false;
            }

            return true;
        }

        BigFileUploader.prototype.handleError = function (data) {
            if (typeof this.options.error === 'function') {
                this.options.error(data);
            }
        }

        BigFileUploader.prototype.handleSuccess = function (data) {
            if (typeof this.options.success === 'function') {
                this.options.success(data);
            }
        }

        BigFileUploader.prototype.handleProgress = function (data) {
            if (typeof this.options.progress === 'function') {
                this.options.progress(data);
            }
        }

        BigFileUploader.prototype.postBlock = function (file, sum, count, index, start, end) {
            var self = this;

            return function () {
                var deferred = $.Deferred(),
                    fd = new FormData();

                fd.append('data', file.slice(start, end));
                fd.append('name', file.name);
                fd.append('sum', sum);
                fd.append('count', count);
                fd.append('index', index);

                return $.ajax({
                    url: self.options.url,
                    type: 'POST',
                    data: fd,
                    async: true,
                    processData: false,
                    contentType: false
                }).done(function (data) {
                    eval("var data = " + data);
                    if (typeof data.status === 'number' && data.status == 0) {
                        var percent = index + 1 === count ? 1 : ((index + 1) / count);

                        self.handleProgress({
                            percent: parseFloat(percent * 100).toFixed(2)
                        });

                        if (data.done) {
                            self.handleSuccess({
                                message: '文件上传完成'
                            });
                        }

                        deferred.resolve();
                    } else {
                        self.handleError({
                            message: data.message
                        });

                        deferred.reject();
                    }
                }).fail(function () {
                    self.handleError({
                        message: '文件上传错误'
                    });

                    deferred.reject();
                });

                return deferred.promise();
            }
        }

        BigFileUploader.prototype.post = function () {
            var self = this,
                file = this.file.files[0],
                blockSize = 1024 * 1024 * this.options.blockSize,
                blockCount = Math.ceil(file.size / blockSize),
                sum = (new Date()).getTime() + '-' + Math.floor(Math.random() * file.size),
                promise = $.Deferred().resolve();

            for (var i = 0; i < blockCount; i++) {
                var start = blockSize * i,
                    end = Math.min(file.size, start + blockSize);

                promise = promise.then(self.postBlock(file, sum, blockCount, i, start, end));
            }
        }

        BigFileUploader.prototype.start = function () {
            if (true === this.init()) {
                this.post();
            }
        }
    }
}