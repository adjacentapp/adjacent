import { Component, ViewChild } from '@angular/core';
import { Platform, MenuController, Nav, AlertController } from 'ionic-angular';

import { DiscoverPage } from '../pages/discover/discover';
import { ProfilePage } from '../pages/profile/profile';
import { NewCardPage } from '../pages/card/new';
import { BookmarksPage } from '../pages/bookmarks/bookmarks';
import { ShowCardPage } from '../pages/card/show';
import { MessagesPage } from '../pages/messages/index';
import { FoundedPage } from '../pages/founded/founded';
// import { MissionPage } from '../pages/mission/mission';


import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { GoogleAnalytics } from '@ionic-native/google-analytics';
import { Deeplinks } from '@ionic-native/deeplinks';

import { AuthProvider } from '../providers/auth/auth';
import { GlobalsProvider } from '../providers/globals/globals';
import { MessagesProvider } from '../providers/messages/messages';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  // rootPage:any = 'LoginPage';
  profPage:{title: string, component: any, icon: string} = { title: 'Profile', component: ProfilePage, icon: 'md-person' };
  pages: Array<{title: string, component: any, icon: string, badge?: number}> = [
    { title: 'Pitch an Idea', component: NewCardPage, icon: 'md-create' },
    { title: 'My Ideas', component: FoundedPage, icon: 'md-bulb' },
    { title: "Following", component: BookmarksPage, icon: 'md-bookmark' },
    { title: "Messages", component: MessagesPage, icon: 'md-paper-plane', badge: 0 },
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
    private globs: GlobalsProvider,
    private msg: MessagesProvider,
    private alertCtrl: AlertController,
    private deeplinks: Deeplinks
  ){
    this.getGlobs();
    this.platform.ready().then( () => {
        this.statusBar.styleDefault();
        this.splashScreen.hide();

        this.checkCookies();

        this.initGA();
        this.checkDeepLink();
        this.checkDeepNotifications();
        this.listenForMessages();
    });
  }

  listenForMessages(){
    let user_id = this.auth.currentUser ? this.auth.currentUser.id : 0;
    this.msg.getUnreadCount(user_id)
      .subscribe(
        data => {
          // this.pages.reduce( (prev, curr) => curr.title === "Messages" ? curr : prev ).badge = data.length;
        },
        error => console.log(<any>error),
        () => setTimeout(() => {
          this.listenForMessages();
        }, 5000)
      );
  }

  getGlobs() {
    this.globs.getGlobs()
      .subscribe(
        data => console.log(data),
        error => console.log(<any>error)
      );
  }

  initGA() {
    this.ga.startTrackerWithId('UA-86377634-1')
       .then(() => {
         console.log('Google analytics is ready now');
         this.ga.trackView('test');
         // Tracker is ready
         // You can now track pages or set additional information such as AppVersion or UserId
       })
       .catch(e => console.log('Error starting GoogleAnalytics', e));
  }

  checkDeepLink() {
    console.log('deep linking');
    this.deeplinks.routeWithNavController(this.nav, {
      '/idea/:card_id': ShowCardPage,
      '/user/:user_id': ProfilePage,
      '/message/:conversationId': MessagesPage
    }).subscribe((match) => {
        // match.$route - the route we matched, which is the matched entry from the arguments to route()
        // match.$args - the args passed in the link
        // match.$link - the full link data
        console.log('Successfully matched route', match);
        let split = match.$link.path.split('/');
        let id = split[ split.length-1 ];
        let object = split[ split.length-2 ];
        if(object == 'idea')
          this.nav.push(ShowCardPage, {
            card_id: id
          });
      }, (nomatch) => {
        // nomatch.$link - the full link data
        console.log('Got a deeplink that didn\'t match', nomatch);
      });
  }

  checkDeepNotifications() {
    // We subscribe to the dismiss observable of the service
    this.auth.checkDeepNotifications.subscribe((value) => {
      if (!value)
        return;
      else if (value == 'nc')
        this.menu.open('right');
      else {
        this.nav.popToRoot();
        if (value == 'message')
          this.nav.push(MessagesPage);
        else
          this.nav.push(ShowCardPage, {card_id: value});
      }
    });
  }

  openPage(page) {
    this.menu.close();
    this.nav.push(page.component);
  }

  goToCard(item) {
    this.menu.close();
    this.nav.push(ShowCardPage, {
      item: item
    });
  }

  jumpToCard(item){
    this.menu.close();
    let handleDelete = (_params) => {
      return new Promise((resolve, reject) => {
        resolve();
      });
    }
    let handleUpdate = (updated) => {
      return new Promise((resolve, reject) => {
        resolve();
      });
    }
    this.nav.push(
      ShowCardPage, {
        item: item,
        deleteCallback: handleDelete,
        updateCallback: handleUpdate
      },
      { animate: false }
    );
  }

  goToProfile(item) {
    this.menu.close();
    this.nav.push(ProfilePage, {
      user_id: item
    });
  }

  goToMission() {
    this.menu.close();
    this.nav.push('MissionPage');
  }

  checkCookies() {
    // immediately go to discover if cookies found
    if(localStorage.token && localStorage.user_id) {
      this.auth.loginFromLocalStorage();
      this.nav.setRoot(DiscoverPage);
      // if token authentication issue, redirect back to login
      this.auth.checkToken(localStorage.token, localStorage.user_id).subscribe(
        user => {
          if(!this.auth.valid) {
            localStorage.clear();
            this.showError("Something went wrong. Please sign in again.");
            this.nav.setRoot('LoginPage');
          }
        },
        error => {
            console.log(error);
            // this.showError(error);
        });
    }
    else
      this.nav.setRoot('LoginPage');
  }

  logout() {
    let alert = this.alertCtrl.create({
      title: 'Logout?',
      message: 'Are you sure you want to logout?',
      buttons: [{
          text: 'Cancel',
          role: 'cancel',
          handler: () => console.log('Cancel clicked')
        },
        {
          text: 'Logout',
          handler: () => {
            this.auth.logout().subscribe(succ => {
              this.menu.close();
              this.nav.setRoot('LoginPage');
            });
          }
      }]
    });
    alert.present();
  }

  showError(text) {
    let alert = this.alertCtrl.create({
      title: 'Uh Oh',
      subTitle: text,
      buttons: ['OK']
    });
    alert.present(prompt);
  }
}
