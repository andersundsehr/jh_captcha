import * as Helper from '@typo3/form/backend/form-editor/helper.js';

let formEditorApp = null;

function getFormEditorApp() {
    return formEditorApp;
}

function getPublisherSubscriber() {
    return getFormEditorApp().getPublisherSubscriber();
}

function assert(test, message, messageCode) {
    return getFormEditorApp().assert(test, message, messageCode);
}

function helperSetup() {
    assert(
        typeof Helper.bootstrap === 'function',
        'The view model helper does not implement the method "bootstrap"',
        1491643380
    );
    Helper.bootstrap(getFormEditorApp());
}

function subscribeEvents() {
    getPublisherSubscriber().subscribe('view/stage/abstract/render/template/perform', (topic, args) => {
        if (args[0].get('type') === 'JhCaptchaRecaptcha') {
            getFormEditorApp().getViewModel().getStage().renderSimpleTemplateWithValidators(args[0], args[1]);
        }
    });
}

export function bootstrap(currentFormEditorApp) {
    formEditorApp = currentFormEditorApp;
    helperSetup();
    subscribeEvents();
}
