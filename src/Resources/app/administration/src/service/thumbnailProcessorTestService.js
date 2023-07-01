const ApiService = Shopware.Classes.ApiService;
const { Application } = Shopware;

class ApiClient extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'thumbnail-processor-test') {
        super(httpClient, loginService, apiEndpoint);
    }

    getUrl(salesChannelId) {
        const headers = this.getBasicHeaders({});

        return this.httpClient
            .post(`_action/${this.getApiBasePath()}/get-sample-image`, {'salesChannelId': salesChannelId},{
                headers
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

Application.addServiceProvider('thumbnailProcessorTest', (container) => {
    const initContainer = Application.getContainer('init');
    return new ApiClient(initContainer.httpClient, container.loginService);
});
