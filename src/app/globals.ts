'use strict';

let DEV = false;

let localURL = "http://localhost/~salsaia/adjacent/api/v3/";
let remoteURL = "http://adjacent-env.btwkki4rra.us-west-2.elasticbeanstalk.com/api/v3/"

export const ENCRYPTION_KEY = "aether12292015";

export const BASE_API_URL = DEV ? localURL : remoteURL;

export const INDUSTRIES: string[] = [
	'Agriculture',
	'Art',
	'Architecture',
	'Business',
	'Computer Science',
	'Design',
	'Education',
	'Engineering',
	'Environment',
	'Fashion',
	'Finance',
	'Gaming',
	'Government',
	'Healthcare',
	'Humanities',
	'Journalism',
	'Language',
	'Law',
	'Lifestyle',
	'Marketing',
	'Math',
	'Music',
	'Performing Arts',
	'Science',
	'Social Impact',
	'Sports',
	'Transportation',
	'Urban Planning',
	'Writing'
];

export const STAGES: string[] = [
	'Couch Entrepreneur',
	'Taking First Step',
	'"Kickstarting" The Engine',
	'Running The Company',
	'Scaling The Business',
	'$$$'
];

export let SKILLS: any[] = [
	{ id: '1', name: 'Business' },
	{ id: '2', name: 'Customer Discovery' },
	{ id: '3', name: 'Design' },
	{ id: '4', name: 'Development' },
	{ id: '5', name: 'Marketing' },
	{ id: '6', name: 'User Experience' },
	{ id: '7', name: 'Customer discovery' }
];
export let setSkills = (skills) => {
	SKILLS = skills;
};

export let SHARE_URL: string = 'adjacentapp.com';
export let setShareURL = (url) => {
  SHARE_URL = url;
};

export const NETWORKS: string[] = [
	'Public',
	'New York University',
	'NYU Game Centr',
	'ThinkLab Incubator',
	'HCI Meetup'
];

export let firstFollow = true;
export let setFirstFollowFalse = () => {
	firstFollow = false;
};
