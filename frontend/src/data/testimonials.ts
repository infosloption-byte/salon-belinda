export interface Testimonial {
  id: string;
  name: string;
  service: string;
  rating: number;
  message: string;
  date: string;
}

// In the backend phase, this list will be replaced by an API call, and new
// submissions from the Reviews page will POST to that same endpoint.
export const testimonials: Testimonial[] = [
  {
    id: 't1',
    name: 'Nimasha Perera',
    service: 'Complete Bridal Package',
    rating: 5,
    message:
      'The team made my wedding morning so calm. My hair held up through the whole beach ceremony and photos afterwards. I felt completely like myself, just more polished.',
    date: '2026-05-12',
  },
  {
    id: 't2',
    name: 'Ishara Fernando',
    service: 'Bridal Trial Session',
    rating: 5,
    message:
      'Booked a trial two weeks before the big day and it saved me. We adjusted the eye makeup and I walked in on the day already knowing exactly what to expect.',
    date: '2026-04-02',
  },
  {
    id: 't3',
    name: 'Dilhani Wickramasinghe',
    service: 'Occasion Updo',
    rating: 4,
    message:
      'Lovely updo for my sister\'s homecoming. Only reason it\'s not five stars is I wish I\'d booked more time for the trial beforehand.',
    date: '2026-03-20',
  },
  {
    id: 't4',
    name: 'Sanduni Rathnayake',
    service: 'Classic Facial',
    rating: 5,
    message:
      'My go-to facial before any event. The salon always feels clean, relaxed, and the staff remember exactly what my skin needs each visit.',
    date: '2026-02-14',
  },
  {
    id: 't5',
    name: 'Kavindi Jayasuriya',
    service: 'Gel Overlay & Art',
    rating: 5,
    message:
      'Asked for something bridal-adjacent but subtle for a friend\'s wedding and they nailed it — pun intended. Lasted three full weeks.',
    date: '2026-01-30',
  },
];
