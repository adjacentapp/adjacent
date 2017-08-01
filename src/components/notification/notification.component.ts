import { Component, Input } from '@angular/core';

@Component({
  selector: 'notification-component',
  templateUrl: 'notification-component.html'
})
export class NotificationComponent {
  @Input() item: any;

  constructor() {}
}
