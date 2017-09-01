import { Component, Input } from '@angular/core';
import { NavController } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';

@Component({
  selector: 'message',
  templateUrl: 'message.html'
})
export class MessageComponent {
  @Input() item: any;
  @Input() founder_id: number;
  founder: boolean = false;
  other: boolean;

  constructor(public navCtrl: NavController, private auth: AuthProvider) {}

  ngOnInit() {
  	this.other = this.item.user.id !== this.auth.currentUser.id;
    this.founder = this.item.user.id === this.founder_id || !this.item.user.id;
    // if user.id === null then there's no user, meaning this was excluded bc they're the card's founder
  }

  avatarTapped(event, item){
    event.stopPropagation();
  }
}
