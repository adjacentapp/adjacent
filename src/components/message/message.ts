import { Component, Input } from '@angular/core';
import { AuthProvider, User } from '../../providers/auth/auth';

@Component({
  selector: 'message',
  templateUrl: 'message.html'
})
export class MessageComponent {
  // item: {user: User, text: string};
  @Input() item: any;
  other: boolean;

  constructor( private auth: AuthProvider ){}

  ngOnInit() {
  	this.other = this.item.user.id !== this.auth.currentUser.id;
  }
}
