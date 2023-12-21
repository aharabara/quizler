/// <reference types='codeceptjs' />
type steps_file = typeof import('./tests/steps_file.js');
type Auth = import('./tests/helpers/auth_helper.js');

declare namespace CodeceptJS {
  interface SupportObject { I: I, current: any, loginAs: any }
  interface Methods extends Playwright, Auth {}
  interface I extends ReturnType<steps_file>, WithTranslation<Auth> {}
  namespace Translation {
    interface Actions {}
  }
}
