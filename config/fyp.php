<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FYP Phase Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the FYP Phase Management System.
    |
    */

    /**
     * Available phases with their slugs and display names
     */
    'phases' => [
        'idea_approval' => [
            'name' => 'Idea Approval',
            'slug' => 'idea_approval',
            'order' => 1,
            'description' => 'Students submit project ideas for supervisor approval.',
            'project_phase' => 'idea',
        ],
        'scope_approval' => [
            'name' => 'Scope Approval',
            'slug' => 'scope_approval',
            'order' => 2,
            'description' => 'Students submit scope documents for review and approval.',
            'project_phase' => 'scope',
        ],
        'defence' => [
            'name' => 'Defence',
            'slug' => 'defence',
            'order' => 3,
            'description' => 'Final project defence sessions are scheduled and conducted.',
            'project_phase' => 'defence',
        ],
    ],

    /**
     * Project phase values
     */
    'project_phases' => [
        'idea' => 'Idea Approval',
        'scope' => 'Scope Approval',
        'defence' => 'Defence',
        'completed' => 'Completed',
    ],

    /**
     * Project statuses
     */
    'project_statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
    ],

    /**
     * Scope document statuses
     */
    'scope_statuses' => [
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'revision_required' => 'Revision Required',
    ],

    /**
     * Phase submission statuses
     */
    'submission_statuses' => [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'submitted' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'revision_required' => 'Revision Required',
    ],

    /**
     * Status colors for badges (Tailwind CSS classes)
     */
    'status_colors' => [
        // Phase status colors
        'upcoming' => 'blue',
        'active' => 'green',
        'ended' => 'gray',
        'inactive' => 'red',

        // Document/Submission status colors
        'pending' => 'yellow',
        'approved' => 'green',
        'rejected' => 'red',
        'revision_required' => 'orange',
        'not_started' => 'gray',
        'in_progress' => 'blue',
        'submitted' => 'yellow',

        // Project phase colors
        'idea' => 'blue',
        'scope' => 'yellow',
        'defence' => 'purple',
        'completed' => 'green',
    ],

    /**
     * Default settings
     */
    'defaults' => [
        'allow_late_submission' => false,
        'phase_duration_weeks' => 4,
    ],

];