import { Component, ViewChild } from '@angular/core';
import { Platform, MenuController, Nav, AlertController } from 'ionic-angular';

import { DiscoverPage } from '../pages/discover/discover';
import { ProfilePage } from '../pages/profile/profile';
import { NewCardPage } from '../pages/card/new';
import { BookmarksPage } from '../pages/bookmarks/bookmarks';

import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { GoogleAnalytics } from '@ionic-native/google-analytics';

import { AuthProvider } from '../providers/auth/auth';
import { GlobalsProvider } from '../providers/globals/globals';


@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  rootPage:any = 'LoginPage';
  profPage:{title: string, component: any} = { title: 'Profile', component: ProfilePage };
  pages: Array<{title: string, component: any}> = [
    { title: 'Pitch My Idea', component: NewCardPage },
    // { title: 'Discover', component: DiscoverPage },
    // { title: 'Profile', component: ProfilePage },
    { title: "Ideas I'm Following", component: BookmarksPage },
  ];
  username = '';
  email = '';

  constructor(
    public platform: Platform,
    public menu: MenuController,
    public statusBar: StatusBar,
    public splashScreen: SplashScreen,
    public ga: GoogleAnalytics,
    private auth: AuthProvider,
    private globsProv: GlobalsProvider,
    private alertCtrl: AlertController
  ){
    this.initializeApp();
    this.checkDeepLink();
    this.getGlobs();
  }

  public logout() {
    let alert = this.alertCtrl.create({
      title: 'Logout?',
      message: 'Are you sure you want to logout?',
      buttons: [
        {
          text: 'Cancel',
          role: 'cancel',
          handler: () => {
            console.log('Cancel clicked');
          }
        },
        {
          text: 'Logout',
          handler: () => {
            this.auth.logout().subscribe(succ => {
              this.menu.close();
              this.nav.setRoot('LoginPage');
            });
          }
        }
      ]
    });
    alert.present();
  }

  checkDeepLink() {
    console.log('deep linking');
  }

  getGlobs() {
    this.globsProv.getGlobs()
      .subscribe(
        data => console.log(data),
        error => console.log(<any>error)
      );
  }

  initializeApp() {
    this.platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      this.statusBar.styleDefault();
      this.splashScreen.hide();

      this.ga.startTrackerWithId('UA-86377634-1')
         .then(() => {
           console.log('Google analytics is ready now');
              this.ga.trackView('test');
           // Tracker is ready
           // You can now track pages or set additional information such as AppVersion or UserId
         })
         .catch(e => console.log('Error starting GoogleAnalytics', e));
    });
  }

  openPage(page) {
    this.menu.close();
    this.nav.push(page.component);
  }
}
